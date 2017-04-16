package builder

import (
    "github.com/docker/docker/builder"
    "github.com/docker/docker/builder/dockerignore"
    "github.com/docker/docker/pkg/archive"
    "github.com/docker/docker/pkg/fileutils"
    "github.com/pkg/errors"
    "os"
    "path/filepath"
    "io"
    "fmt"
    "github.com/docker/docker/cli"
    "github.com/docker/docker/pkg/term"
    "github.com/docker/docker/cli/command"
    "github.com/docker/docker/pkg/jsonmessage"
    "github.com/docker/docker/reference"
    "github.com/docker/docker/registry"
)

// CreateBuildContext will create the Docker build context from the current directory,
// based on the manifest configuration.
func CreateBuildContext(manifest Manifest) (io.ReadCloser, error) {
    contextDir, relDockerfile, err := builder.GetContextFromLocalDir(".", manifest.DockerfilePath)

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
func ReadDockerResponse(responseBody io.ReadCloser) error {
    defer responseBody.Close()

    _, stdout, _ := term.StdStreams()

    out := command.NewOutStream(stdout)
    err := jsonmessage.DisplayJSONMessagesStream(responseBody, out, out.FD(), out.IsTerminal(), nil)
    if err != nil {
        if jerr, ok := err.(*jsonmessage.JSONError); ok {
            // If no error code is set, default to 1
            if jerr.Code == 0 {
                jerr.Code = 1
            }

            fmt.Println(jerr.Code, ": ", jerr.Message)

            return cli.StatusError{Status: jerr.Message, StatusCode: jerr.Code}
        }

        return err
    }

    return nil
}

// CreatePushRegistryAuth will create the auth registry string required by the push image configuration.
func CreatePushRegistryAuth(manifest Manifest) (string, error) {
    ref, err := reference.ParseNamed(manifest.Name)
    if err != nil {
        return "", err
    }

    repoInfo, err := registry.ParseRepositoryInfo(ref)
    if err != nil {
        return "", err
    }

    authConfig, found := manifest.AuthConfigs[repoInfo.Index.Name];
    if !found {
        return "", fmt.Errorf("No authentication configuration found for the registry \"%s\"", repoInfo.Index.Name)
    }

    encodedAuth, err := command.EncodeAuthToBase64(authConfig)
    if err != nil {
        return "", err
    }

    return encodedAuth, nil
}
