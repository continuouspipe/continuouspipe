package builder

import (
    "github.com/docker/docker/pkg/archive"
    "github.com/docker/docker/pkg/fileutils"
    "github.com/pkg/errors"
    "os"
    "path/filepath"
    "io"
    "fmt"
    "github.com/docker/docker/pkg/term"
    "github.com/docker/docker/pkg/jsonmessage"
    "github.com/docker/docker/reference"
    "github.com/docker/docker/builder"
    "github.com/docker/docker/builder/dockerignore"
    "encoding/base64"
    "encoding/json"
    "net/http"
    "compress/gzip"
    "archive/tar"
)

// CreateBuildContext will create the Docker build context from the current directory,
// based on the manifest configuration.
func CreateBuildContext(step ManifestStep) (io.ReadCloser, error) {
    if step.ArchiveSource.Url != "" {
        // Get the data
        request, err := http.NewRequest("GET", step.ArchiveSource.Url, nil)
        if err != nil {
            return nil, err
        }

        for key, value := range step.ArchiveSource.Headers {
            request.Header.Set(key, value)
        }

        response, err := http.DefaultClient.Do(request)
        if err != nil {
            return nil, err
        }

        defer response.Body.Close()
        if response.StatusCode < 200 || response.StatusCode > 399 {
            return nil, fmt.Errorf("Unable to download the source code (%d)", response.StatusCode)
        }

        // Writer the body to file
        gzr, err := gzip.NewReader(response.Body)
        if err != nil {
            return nil, fmt.Errorf("Response was not expected: %v", err)
        }
        defer gzr.Close()

        err = untar(tar.NewReader(gzr), "source-code", true)
        if err != nil {
            return nil, err
        }
    }
    // FIXME The "build directory" configuration is not supported when the code is not downloaded.
    buildDirectory := GetBuildDirectory(step)

    contextDir, relDockerfile, err := builder.GetContextFromLocalDir(buildDirectory, buildDirectory+"/"+step.DockerfilePath)

    // And canonicalize dockerfile name to a platform-independent one
    relDockerfile, err = archive.CanonicalTarNameForPath(relDockerfile)
    if err != nil {
        return nil, errors.Errorf("cannot canonicalize dockerfile path %s: %v", relDockerfile, err)
    }

    f, err := os.Open(filepath.Join(contextDir, ".dockerignore"))
    if err != nil && !os.IsNotExist(err) {
        return nil, err
    }
    defer f.Close()

    var excludes []string
    if err == nil {
        excludes, err = dockerignore.ReadAll(f)
        if err != nil {
            return nil, err
        }
    }

    if err := builder.ValidateContextDirectory(contextDir, excludes); err != nil {
        return nil, errors.Errorf("Error checking context: '%s'.", err)
    }

    // If .dockerignore mentions .dockerignore or the Dockerfile then make
    // sure we send both files over to the daemon because Dockerfile is,
    // obviously, needed no matter what, and .dockerignore is needed to know
    // if either one needs to be removed. The daemon will remove them
    // if necessary, after it parses the Dockerfile. Ignore errors here, as
    // they will have been caught by validateContextDirectory above.
    // Excludes are used instead of includes to maintain the order of files
    // in the archive.
    if keep, _ := fileutils.Matches(".dockerignore", excludes); keep {
        excludes = append(excludes, "!.dockerignore")
    }
    if keep, _ := fileutils.Matches(relDockerfile, excludes); keep  {
        excludes = append(excludes, "!"+relDockerfile)
    }

    return archive.TarWithOptions(contextDir, &archive.TarOptions{
        Compression:     archive.Gzip,
        ExcludePatterns: excludes,
    })
}

// ReadDockerResponse will read a display to the UI the responses coming from the
// docker daemon.
func ReadDockerResponse(responseBody io.ReadCloser, output io.Writer) error {
    defer responseBody.Close()

    outputFileDescriptor, outputIsTerminal := term.GetFdInfo(output)

    err := jsonmessage.DisplayJSONMessagesStream(responseBody, output, outputFileDescriptor, outputIsTerminal, nil)
    if err != nil {
        if jerr, ok := err.(*jsonmessage.JSONError); ok {
            // If no error code is set, default to 1
            if jerr.Code == 0 {
                jerr.Code = 1
            }

            fmt.Println(jerr.Code, ": ", jerr.Message)

            return fmt.Errorf("%s [%d]", jerr.Message, jerr.Code)
        }

        return err
    }

    return nil
}

// CreatePushRegistryAuth will create the auth registry string required by the push image configuration.
func CreatePushRegistryAuth(manifest Manifest, imageName string) (string, error) {
    ref, err := reference.ParseNamed(imageName)
    if err != nil {
        return "", err
    }

    authConfig, found := manifest.AuthConfigs[ref.Hostname()];
    if !found {
        return "", fmt.Errorf("No authentication configuration found for the registry \"%s\"", ref.Hostname())
    }

    buf, err := json.Marshal(authConfig)
    if err != nil {
        return "", err
    }

    return base64.URLEncoding.EncodeToString(buf), nil
}

func GetBuildDirectory(step ManifestStep) string {
    if step.ArchiveSource.Url != "" {
        return  "./source-code/"+step.BuildDirectory
    }
    return step.BuildDirectory
}
