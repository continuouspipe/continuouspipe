package main

import (
    "github.com/continuouspipe/cloud-builder/builder"

    "flag"
    "fmt"
    "os"
    "github.com/docker/engine-api/client"
    "cloud.google.com/go/storage"
    "golang.org/x/net/context"
    "io/ioutil"
    "encoding/json"
    "google.golang.org/cloud"
    "golang.org/x/oauth2/google"
    "golang.org/x/oauth2/jwt"
    "golang.org/x/oauth2"
)

func main() {
    manifestFilePath := flag.String("manifest", "continuouspipe.build-manifest.json", "the build manifest to be used to build")
    googleServiceAccountFilePath := flag.String("service-account-file-path", "", "the path of the service account to use, if any")
    firebaseServiceAccountFilePath := flag.String("firebase-service-account-file-path", "", "the path of the service account to use, if any")
    deleteManifestFile := flag.Bool("delete-namifest", false, "Delete the manifest after reading")

    flag.Parse()

    manifest, err := builder.ReadManifest(*manifestFilePath)
    if err != nil {
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
            fmt.Println(err)
            os.Exit(1)
        }
    }

    stepRunner, err := NewStepRunner(manifest)
    if err != nil {
        fmt.Println(err)
        os.Exit(1)
    }

    args := flag.Args()
    if len(args) > 0 && args[0] == "check" {
        if err = stepRunner.Check(); err != nil {
            fmt.Println(err)
            os.Exit(1)
        }

        os.Exit(0)
    }

    // Add the firebase logging decorator if requested
    if "" != manifest.FirebaseLoggingConfiguration.DatabaseUrl {
        if "" != *firebaseServiceAccountFilePath {
            manifest.FirebaseLoggingConfiguration.ServiceAccount, err = ServiceAccountConfigurationFromFile(*firebaseServiceAccountFilePath)

            if err != nil {
                fmt.Println(err)
                os.Exit(1)
            }
        }

        stepRunner, err = builder.NewFirebaseLoggedStepRunner(
            stepRunner,
            manifest.FirebaseLoggingConfiguration,
        )

        if err != nil {
            fmt.Println(err)
            os.Exit(1)
        }
    }

    buildRunner := builder.NewBuildRunner(stepRunner)
    err = buildRunner.Run(manifest)

    if err != nil {
        fmt.Println(err)
        os.Exit(1)
    }

    fmt.Println(manifest.LogBoundary+"::END")
}

func NewStepRunner (manifest builder.Manifest) (builder.StepRunner, error) {
    docker, err := client.NewEnvClient()
    if err != nil {
        return nil, err
    }

    ctx := context.Background()
    if "" != manifest.ArtifactsConfiguration.ServiceAccount.PrivateKey {
        conf := &jwt.Config{
            Email:      manifest.ArtifactsConfiguration.ServiceAccount.Email,
            PrivateKey: []byte(manifest.ArtifactsConfiguration.ServiceAccount.PrivateKey),
            Scopes:     []string{storage.ScopeFullControl},
            TokenURL:   google.JWTTokenURL,
        }

        ctx = cloud.NewContext(manifest.ArtifactsConfiguration.ServiceAccount.ProjectId, conf.Client(oauth2.NoContext))
    }

    storageClient, err := storage.NewClient(ctx)
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
