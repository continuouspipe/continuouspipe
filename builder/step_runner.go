package builder

import (
    "os"
    "os/exec"
    "golang.org/x/net/context"
    "github.com/docker/engine-api/client"
    "github.com/docker/engine-api/types"
    "github.com/docker/engine-api/types/container"
    "fmt"
    "github.com/docker/engine-api/types/network"
    "strconv"
)

// StepRunner is responsible of running a step
type StepRunner interface {
    Run(manifest Manifest, step ManifestStep) error
    CleanUp(step ManifestStep) error
    Check() error
}

// DockerStepRunner is a step runner based on the docker client
type DockerStepRunner struct {
    dockerClient *client.Client
    artifactManager ArtifactManager
}

// DockerStepRunner creates an instance of DockerStepRunner
func NewDockerStepRunner(client *client.Client, artifactManager ArtifactManager) (*DockerStepRunner, error) {
    return &DockerStepRunner{
        dockerClient: client,
        artifactManager: artifactManager,
    }, nil
}

// Run will run the given step
func (sr *DockerStepRunner) Run(manifest Manifest, step ManifestStep) error {
    for _, artifact := range step.ReadArtifacts {
        Display(manifest, fmt.Sprintf("Reading artifact \"%s\"", artifact.Name))
        if err := sr.readArtifact(step, artifact); err != nil {
            return err
        }
    }

    Display(manifest, fmt.Sprintf("Building Docker image %s", ImageNameForDisplay(step)))
    builtImage, err := sr.buildImage(manifest, step)
    if err != nil {
        return err
    }

    if step.ImageName != "" {
        Display(manifest, fmt.Sprintf("Pushing Docker image %s", ImageNameForDisplay(step)))

        if err = sr.pushImage(manifest, step); err != nil {
            return err
        }
    }

    for _, artifact := range step.WriteArtifacts {
        Display(manifest, fmt.Sprintf("Writing artifact \"%s\"", artifact.Name))
        if err := sr.writeArtifact(step, builtImage, artifact); err != nil {
            return err
        }
    }

    Display(manifest, "DONE")

    return nil
}

func (sr *DockerStepRunner) CleanUp(step ManifestStep) error {
    for _, artifact := range step.ReadArtifacts {
        downloadedPath := GetLocalArtifactTarget(step, artifact)

        if err := exec.Command("rm", "-rf", downloadedPath).Run(); err != nil {
            return err
        }
    }

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

func (sr DockerStepRunner) readArtifact(step ManifestStep, artifact Artifact) error {
    return sr.artifactManager.ReadTo(artifact, GetLocalArtifactTarget(step, artifact))
}

func (sr DockerStepRunner) writeArtifact(step ManifestStep, builtImage string, artifact Artifact) error {
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

func (sr DockerStepRunner) buildImage(manifest Manifest, step ManifestStep) (string, error) {
    ctx := context.Background()

    buildCtx, err := CreateBuildContext(step)
    if err != nil {
        return "", err
    }

    if "" == step.ImageName {
        step.ImageName = "step"
    }

    response, err := sr.dockerClient.ImageBuild(ctx, buildCtx, types.ImageBuildOptions{
        AuthConfigs: manifest.AuthConfigs,

        Tags: []string{step.ImageName},
        Dockerfile: step.DockerfilePath,
        BuildArgs: step.BuildArgs,
    })

    if err != nil {
        return "", err
    }

    return step.ImageName, ReadDockerResponse(response.Body)
}

func (sr DockerStepRunner) pushImage(manifest Manifest, step ManifestStep) error {
    ctx := context.Background()
    authConfig, err := CreatePushRegistryAuth(manifest, step.ImageName)
    if err != nil {
        return err
    }

    response, err := sr.dockerClient.ImagePush(ctx, step.ImageName, types.ImagePushOptions{
        RegistryAuth: authConfig,
    })

    if err != nil {
        return err
    }

    return ReadDockerResponse(response)
}

func GetLocalArtifactTarget(step ManifestStep, artifact Artifact) string {
    return step.BuildDirectory+string(os.PathSeparator)+artifact.Path
}

func ImageNameForDisplay(step ManifestStep) string {
    if "" == step.ImageName {
        return "for step #"+strconv.Itoa(step.Number)
    }

    return "\""+step.ImageName+"\""
}

func Display(manifest Manifest, title string) {
    fmt.Println(manifest.LogBoundary+"::"+title)
}
