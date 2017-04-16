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
    flag.Parse()

    manifest, err := builder.ReadManifest(*manifestFilePath)
    if err != nil {
        fmt.Println(err)
        os.Exit(1)
    }

    runner, err := NewRunner(manifest, *googleServiceAccountFilePath)
    if err != nil {
        fmt.Println(err)
        os.Exit(1)
    }

    args := flag.Args()
    if len(args) > 0 && args[0] == "check" {
        if err = runner.Check(); err != nil {
            fmt.Println(err)
            os.Exit(1)
        }

        os.Exit(0)
    }

    for number, step := range manifest.Steps {
        step.Number = number + 1

        if "" == step.BuildDirectory {
            step.BuildDirectory = "."
        }
        if "" == step.DockerfilePath {
            step.DockerfilePath = "Dockerfile"
        }

        if err = runner.Run(manifest, step); err != nil {
            break
        }
    }

    for _, step := range manifest.Steps {
        runner.CleanUp(step)
    }

    if err != nil {
        fmt.Println(err)
        os.Exit(1)
    }

    fmt.Println(manifest.LogBoundary+"::END")
}

func NewRunner (manifest builder.Manifest, googleServiceAccountFilePath string) (builder.StepRunner, error) {
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
