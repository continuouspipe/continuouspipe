package builder

import (
    "fmt"
    "strconv"
    "github.com/docker/docker/pkg/term"
)

// BuildRunner is a step runner
type BuildRunner struct {
    stepRunner StepRunner
}

func NewBuildRunner(stepRunner StepRunner) BuildRunner {
    return BuildRunner{
        stepRunner: stepRunner,
    }
}

func (r BuildRunner) Run(manifest Manifest) error {
    var err error
    for number, step := range manifest.Steps {
        step.Number = number + 1

        if "" == step.BuildDirectory {
            step.BuildDirectory = "."
        }
        if "" == step.DockerfilePath {
            step.DockerfilePath = "Dockerfile"
        }

        if err = r.runStep(manifest, step); err != nil {
            break
        }
    }

    for _, step := range manifest.Steps {
        r.stepRunner.CleanUpWroteArtifacts(step)
    }

    return err
}

func (r BuildRunner) runStep(manifest Manifest, step ManifestStep) error {
    _, stdout, _ := term.StdStreams()

    for _, artifact := range step.ReadArtifacts {
        Display(manifest, fmt.Sprintf("Reading artifact \"%s\"", artifact.Name))
        if err := r.stepRunner.ReadArtifact(step, artifact); err != nil {
            if !artifact.Persistent {
                // Ignore a read problem if the artifact is a persistent artifact
                return err
            }
        }
    }

    Display(manifest, fmt.Sprintf("Building Docker image %s", ImageNameForDisplay(step)))
    builtImage, err := r.stepRunner.BuildImage(manifest, step, stdout)
    if err != nil {
        return err
    }

    if step.ImageName != "" {
        Display(manifest, fmt.Sprintf("Pushing Docker image %s", ImageNameForDisplay(step)))

        if err = r.stepRunner.PushImage(manifest, step, stdout); err != nil {
            return err
        }
    }

    for _, artifact := range step.WriteArtifacts {
        Display(manifest, fmt.Sprintf("Writing artifact \"%s\"", artifact.Name))
        if err := r.stepRunner.WriteArtifact(step, builtImage, artifact); err != nil {
            return err
        }
    }

    r.stepRunner.CleanUpReadArtifacts(step)

    Display(manifest, "DONE")

    return nil
}

func ImageNameForDisplay(step ManifestStep) string {
    if "" == step.ImageName {
        return "for step #"+strconv.Itoa(step.Number)
    }

    return "<code>"+step.ImageName+"</code>"
}

func Display(manifest Manifest, title string) {
    fmt.Println(manifest.LogBoundary+"::"+title)
}
