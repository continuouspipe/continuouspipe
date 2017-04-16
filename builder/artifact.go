package builder

import (
    "io"
    "cloud.google.com/go/storage"
    "golang.org/x/net/context"
    "github.com/docker/docker/pkg/archive"
)

// Artifact is something that will be shared across steps or builds
type Artifact struct {
    Name string       `json:"name"`
    Identifier string `json:"identifier"`
    Path string       `json:"path"`
    Persistent bool   `json:"persistent"`
}

type ArtifactManager interface {
    ReadTo(artifact Artifact, destination string) error
    WriteFrom(artifact Artifact, reader io.ReadCloser) error
    Remove(artifact Artifact) error
}

type GoogleCloudStorageArtifactManager struct {
    storageClient *storage.Client
    bucketName    string
}

func NewGoogleCloudStorageArtifactManager(storageClient *storage.Client, bucketName string) GoogleCloudStorageArtifactManager {
    return GoogleCloudStorageArtifactManager{
        storageClient: storageClient,
        bucketName: bucketName,
    }
}

func (m GoogleCloudStorageArtifactManager) ReadTo(artifact Artifact, destination string) error {
    ctx := context.Background()
    reader, err := m.storageClient.Bucket(m.bucketName).Object(GetArtifactObjectName(artifact)).NewReader(ctx)
    if err != nil {
        return err
    }

    defer reader.Close()

    return archive.Untar(reader, destination, &archive.TarOptions{
        // Compression: archive.Gzip,
        NoLchown: true,
    })
}

func (m GoogleCloudStorageArtifactManager) WriteFrom(artifact Artifact, reader io.ReadCloser) error {
    ctx := context.Background()

    objectName := GetArtifactObjectName(artifact)
    writer := m.storageClient.Bucket(m.bucketName).Object(objectName).NewWriter(ctx)

    _, err := io.Copy(writer, reader)
    if err != nil {
        return err
    }

    return writer.Close()
}

func (m GoogleCloudStorageArtifactManager) Remove(artifact Artifact) error {
    return m.storageClient.Bucket(m.bucketName).Object(GetArtifactObjectName(artifact)).Delete(context.Background())
}

func GetArtifactObjectName(artifact Artifact) string {
    return artifact.Identifier+".tar"
}
