package main

import (
    "github.com/continuouspipe/cloud-builder/builder"

    "flag"
    "fmt"
    "os"
    "github.com/docker/engine-api/client"
    "cloud.google.com/go/storage"
    "golang.org/x/net/context"
    "google.golang.org/api/option"
)

func main() {
    manifestFilePath := flag.String("manifest", "continuouspipe.build-manifest.json", "the build manifest to be used to build")
    googleServiceAccountFilePath := flag.String("service-account-file-path", "", "the path of the service account to use, if any")
    firebaseServiceAccountFilePath := flag.String("firebase-service-account-file-path", "", "the path of the service account to use, if any")
    firebaseDatabaseUrl := flag.String("firebase-database-url", "", "URL of the firebase database")
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

    stepRunner, err := NewStepRunner(manifest, *googleServiceAccountFilePath)
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
    if "" != manifest.FirebaseParentLog {
        stepRunner, err = builder.NewFirebaseLoggedStepRunner(
            stepRunner,
            *firebaseServiceAccountFilePath,
            *firebaseDatabaseUrl,
            manifest.FirebaseParentLog,
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

func NewStepRunner (manifest builder.Manifest, googleServiceAccountFilePath string) (builder.StepRunner, error) {
    docker, err := client.NewEnvClient()
    if err != nil {
        return nil, err
    }

    ctx := context.Background()
    var storageClient *storage.Client
    if "" == googleServiceAccountFilePath {
        storageClient, err = storage.NewClient(ctx)
    } else {
        storageClient, err = storage.NewClient(ctx, option.WithServiceAccountFile(googleServiceAccountFilePath))
    }

    if err != nil {
        return nil, err
    }

    return builder.NewDockerStepRunner(
        docker,
        builder.NewGoogleCloudStorageArtifactManager(storageClient, manifest.ArtifactsBucket),
    )
}
