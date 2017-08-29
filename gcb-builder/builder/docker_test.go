package builder

import (
    "testing"
    "io"
    "github.com/docker/engine-api/types"
    "bytes"
    "strings"
)

type NullImageBuilder struct {}
func (nib *NullImageBuilder) Build(buildContext io.Reader, options types.ImageBuildOptions, output io.Writer) error {
    return nil
}

type TracedImageBuilder struct {
    decoratedBuilder ImageBuilder

    calls []types.ImageBuildOptions
}
func (nib *TracedImageBuilder) Build(buildContext io.Reader, options types.ImageBuildOptions, output io.Writer) error {
    nib.calls = append(nib.calls, options)

    return nib.decoratedBuilder.Build(buildContext, options, output)
}

func TestItChangesDockerIoServerAddress(t *testing.T) {
    tracedBuilder := &TracedImageBuilder{
        decoratedBuilder: &NullImageBuilder{},
    }

    builder := &CredentialsAwareImageBuilder{
        decoratedBuilder: tracedBuilder,
    }

    var reader = strings.NewReader("foo")
    var b bytes.Buffer
    err := builder.Build(reader, types.ImageBuildOptions{
        AuthConfigs: map[string]types.AuthConfig{
            "quay.io": types.AuthConfig{
                Username: "foo",
                Password: "bar",
            },
            "docker.io": types.AuthConfig{
                Username: "foo",
                Password: "bar",
            },
        },
    }, &b)
    if err != nil {
        t.Error(err)
    }

    if _, ok := tracedBuilder.calls[0].AuthConfigs["docker.io"]; !ok {
        t.Error("Not found docker.io key")
    }

    if _, ok := tracedBuilder.calls[0].AuthConfigs["https://index.docker.io/v1/"]; !ok {
        t.Error("Not found index.docker.io key")
    }
}
