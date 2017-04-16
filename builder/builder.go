package builder

import (
    "golang.org/x/net/context"

    "github.com/docker/engine-api/client"
    "github.com/docker/engine-api/types"
)

// Builder is a object used to manipulate builds
type Builder struct {
    DockerClient *client.Client
}

// NewBuilder creates an instance of Builder
func NewBuilder() (Builder, error) {
    docker, err := client.NewEnvClient()
    if err != nil {
        return Builder{}, err
    }

    return Builder{
        DockerClient: docker,
    }, nil
}

// Build will start the build of the Docker image from the given manifest
func (b Builder) Build(manifest Manifest) error {
    ctx := context.Background()

    buildCtx, err := CreateBuildContext(manifest)
    if err != nil {
        return err
    }

    response, err := b.DockerClient.ImageBuild(ctx, buildCtx, types.ImageBuildOptions{
        Tags: []string{manifest.Name},
        Dockerfile: manifest.DockerfilePath,
        AuthConfigs: manifest.AuthConfigs,
        BuildArgs: manifest.BuildArgs,
    })

    if err != nil {
        return err
    }

    return ReadDockerResponse(response.Body)
}

// Push will push the built Docker image, based on the manifest configuration
func (b Builder) Push(manifest Manifest) error {
    ctx := context.Background()
    authConfig, err := CreatePushRegistryAuth(manifest)
    if err != nil {
        return err
    }

    response, err := b.DockerClient.ImagePush(ctx, manifest.Name, types.ImagePushOptions{
        RegistryAuth: authConfig,
    })

    if err != nil {
        return err
    }

    return ReadDockerResponse(response)
}

func (b Builder) Check() error {
    ctx := context.Background()

    _, err := b.DockerClient.Info(ctx)

    return err
}
