package builder

import (
    "os"
    "os/exec"
    "golang.org/x/net/context"
    "github.com/docker/engine-api/client"
    "github.com/docker/engine-api/types"
    "github.com/docker/engine-api/types/container"
    "github.com/docker/engine-api/types/network"
    "io"
)

// StepRunner is responsible of running a step
type StepRunner interface {
    ReadArtifact(step ManifestStep, artifact Artifact) error
    WriteArtifact(step ManifestStep, builtImage string, artifact Artifact) error
    BuildImage(manifest Manifest, step ManifestStep, output io.Writer) (string, error)
    PushImage(manifest Manifest, step ManifestStep, output io.Writer) error

    CleanUpWroteArtifacts(step ManifestStep) error
    CleanUpReadArtifacts(step ManifestStep) error

    Check() error
}

// DockerStepRunner is a step runner based on the docker client
type DockerStepRunner struct {
    dockerClient    *client.Client
    imagePusher     ImagePusher
    imageBuilder    ImageBuilder
    artifactManager ArtifactManager
}

// DockerStepRunner creates an instance of DockerStepRunner
func NewDockerStepRunner(client *client.Client, artifactManager ArtifactManager) (*DockerStepRunner, error) {
    return &DockerStepRunner{
        imagePusher: &RetryImagePusher{
            decoratedPusher: &DockerImagePush{
                dockerClient: client,
            },
        },
        imageBuilder: &CredentialsAwareImageBuilder{
            &RetryImageBuilder{
                decoratedBuilder: &DockerImageBuilder{
                    dockerClient: client,
                },
            },
        },
        dockerClient: client,
        artifactManager: artifactManager,
    }, nil
}

func (sr *DockerStepRunner) CleanUpReadArtifacts(step ManifestStep) error {
    for _, artifact := range step.ReadArtifacts {
        downloadedPath := GetLocalArtifactTarget(step, artifact)

        if err := exec.Command("rm", "-rf", downloadedPath).Run(); err != nil {
            return err
        }
    }

    return nil
}

func (sr *DockerStepRunner) CleanUpWroteArtifacts(step ManifestStep) error {
    for _, artifact := range step.WriteArtifacts {
        if !artifact.Persistent {
            sr.artifactManager.Remove(artifact)
        }
    }

    return nil
}

func (sr *DockerStepRunner) Check() error {
    ctx := context.Background()

    _, err := sr.dockerClient.Info(ctx)

    return err
}

func (sr DockerStepRunner) ReadArtifact(step ManifestStep, artifact Artifact) error {
    return sr.artifactManager.ReadTo(artifact, GetLocalArtifactTarget(step, artifact))
}

func (sr DockerStepRunner) WriteArtifact(step ManifestStep, builtImage string, artifact Artifact) error {
    ctx := context.Background()

    response, err := sr.dockerClient.ContainerCreate(
        ctx, &container.Config{
            Image: builtImage,
            Cmd: []string{"echo"},
        },
        &container.HostConfig{},
        &network.NetworkingConfig{},
        "",
    )

    if err != nil {
        return err
    }

    reader, _, err := sr.dockerClient.CopyFromContainer(ctx, response.ID, artifact.Path)
    if err != nil {
        return err
    }
    defer reader.Close()

    return sr.artifactManager.WriteFrom(artifact, reader)
}

func (sr DockerStepRunner) BuildImage(manifest Manifest, step ManifestStep, output io.Writer) (string, error) {
    buildCtx, err := CreateBuildContext(step)
    if err != nil {
        return "", err
    }

    if "" == step.ImageName {
        step.ImageName = "step"
    }

    return step.ImageName, sr.imageBuilder.Build(
        buildCtx,
        types.ImageBuildOptions{
            AuthConfigs: manifest.AuthConfigs,

            Tags: []string{step.ImageName},
            Dockerfile: step.DockerfilePath,
            BuildArgs: step.BuildArgs,
        },
        output,
    )
}

func (sr DockerStepRunner) PushImage(manifest Manifest, step ManifestStep, output io.Writer) error {
    authConfig, err := CreatePushRegistryAuth(manifest, step.ImageName)
    if err != nil {
        return err
    }

    return sr.imagePusher.Push(step.ImageName, authConfig, output)
}

func GetLocalArtifactTarget(step ManifestStep, artifact Artifact) string {
    return GetBuildDirectory(step) + string(os.PathSeparator)+artifact.Path
}
