package main

import (
    "github.com/continuouspipe/continuouspipe/gcb-builder/builder"

    "bytes"
    "flag"
    "fmt"
    "os"
    "net/http"
    "github.com/docker/engine-api/client"
    "cloud.google.com/go/storage"
    "io/ioutil"
    "encoding/json"
    "golang.org/x/oauth2/google"
    "golang.org/x/oauth2/jwt"
    "context"
    "errors"
    "google.golang.org/api/option"
)

func main() {
    manifestFilePath := flag.String("manifest", "continuouspipe.build-manifest.json", "the build manifest to be used to build")
    googleServiceAccountFilePath := flag.String("service-account-file-path", "", "the path of the service account to use, if any")
    firebaseServiceAccountFilePath := flag.String("firebase-service-account-file-path", "", "the path of the service account to use, if any")
    deleteManifestFile := flag.Bool("delete-manifest", false, "Delete the manifest after reading")

    flag.Parse()

    manifest, err := builder.ReadManifest(*manifestFilePath)
    if err != nil {
        // Can't properly fail the build as we didn't yet read the manifest
        fmt.Println(err)
        os.Exit(1)
    }

    if *deleteManifestFile {
        if err := os.Remove(*manifestFilePath); err != nil {
            fmt.Println(err)
        }
    }

    if "" != *googleServiceAccountFilePath {
        manifest.ArtifactsConfiguration.ServiceAccount, err = ServiceAccountConfigurationFromFile(*googleServiceAccountFilePath)

        if err != nil {
            FailBuild(manifest, err)
        }
    }

    stepRunner, err := NewStepRunner(manifest)
    if err != nil {
        FailBuild(manifest, err)
    }

    args := flag.Args()
    if len(args) > 0 && args[0] == "check" {
        if err = stepRunner.Check(); err != nil {
            FailBuild(manifest, err)
        }

        SuccessBuild(manifest)
    }

    // Add the firebase logging decorator if requested
    if "" != manifest.FirebaseLoggingConfiguration.DatabaseUrl {
        if "" != *firebaseServiceAccountFilePath {
            manifest.FirebaseLoggingConfiguration.ServiceAccount, err = ServiceAccountConfigurationFromFile(*firebaseServiceAccountFilePath)

            if err != nil {
                FailBuild(manifest, err)
            }
        }

        stepRunner, err = builder.NewFirebaseLoggedStepRunner(
            stepRunner,
            manifest.FirebaseLoggingConfiguration,
        )

        if err != nil {
            FailBuild(manifest, err)
        }
    }
    fmt.Println("start")

    buildRunner := builder.NewBuildRunner(stepRunner)
    err = buildRunner.Run(manifest)

    if err != nil {
        FailBuild(manifest, err)
    }

    fmt.Println(manifest.LogBoundary+"::END")

    SuccessBuild(manifest)
}

func FailBuild(manifest builder.Manifest, error error) {
    SendNotification(manifest.BuildCompleteEndpoint, "ERROR")

    fmt.Println(error)
    os.Exit(1)
}

func SuccessBuild(manifest builder.Manifest) {
    SendNotification(manifest.BuildCompleteEndpoint, "SUCCESS")

    os.Exit(0)
}

func SendNotification(endpoint string, status string) {
    var body = []byte("{\"status\": \""+status+"\"}")

    req, err := http.NewRequest("POST", endpoint, bytes.NewBuffer(body))
    if err != nil {
        panic(err)
    }

    req.Header.Set("Content-Type", "application/json")

    httpClient := &http.Client{}
    response, err := httpClient.Do(req)
    if err != nil {
        panic(err)
    }

    if response.StatusCode != 204 {
        fmt.Println("ERROR: The status code is not the one expected:", response.StatusCode)
    }
}

func NewStepRunner (manifest builder.Manifest) (builder.StepRunner, error) {
    docker, err := client.NewEnvClient()
    if err != nil {
        return nil, err
    }

    if "" == manifest.ArtifactsConfiguration.ServiceAccount.PrivateKey {
        return nil, errors.New("Artifact service account private key is empty")
    }

    conf := &jwt.Config{
        Email:      manifest.ArtifactsConfiguration.ServiceAccount.Email,
        PrivateKey: []byte(manifest.ArtifactsConfiguration.ServiceAccount.PrivateKey),
        Scopes:     []string{storage.ScopeFullControl},
        TokenURL:   google.JWTTokenURL,
    }

    tokenSource := conf.TokenSource(context.TODO())
    storageClient, err := storage.NewClient(context.Background(), option.WithTokenSource(tokenSource))
    if err != nil {
        return nil, err
    }

    return builder.NewDockerStepRunner(
        docker,
        builder.NewGoogleCloudStorageArtifactManager(storageClient, manifest.ArtifactsConfiguration.BucketName),
    )
}

func ServiceAccountConfigurationFromFile(filePath string) (builder.ServiceAccountConfiguration, error) {
    var configuration builder.ServiceAccountConfiguration

    filePathDescriptor, err := ioutil.ReadFile(filePath)
    if err != nil {
        return configuration, err
    }

    err = json.Unmarshal(filePathDescriptor, &configuration)

    return configuration, err
}
