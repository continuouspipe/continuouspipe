package builder

import (
    "github.com/docker/engine-api/types"
    "io/ioutil"
    "encoding/json"
)

type ServiceAccountConfiguration struct {
    Email      string `json:"client_email"`
    PrivateKey string `json:"private_key"`
    ProjectId  string `json:"project_id"`
}

type ArtifactsConfiguration struct {
    BucketName     string                      `json:"bucket_name"`
    ServiceAccount ServiceAccountConfiguration `json:"service_account"`
}

type FirebaseLoggingConfiguration struct {
    DatabaseUrl    string                      `json:"database_url"`
    ServiceAccount ServiceAccountConfiguration `json:"service_account"`
    ParentLog      string                      `json:"parent_log"`
}

// Manifest represents the manifest file used to configure the build
type Manifest struct {
    AuthConfigs                  map[string]types.AuthConfig  `json:"auth_configs"`
    LogBoundary                  string                       `json:"log_boundary"`
    Steps                        []ManifestStep               `json:"steps"`

    ArtifactsConfiguration       ArtifactsConfiguration       `json:"artifacts_configuration"`
    FirebaseLoggingConfiguration FirebaseLoggingConfiguration `json:"firebase_logging_configuration"`
}

// ManifestStep is a step in the manifest
type ManifestStep struct {
    Number         int
    ImageName      string            `json:"image_name"`
    DockerfilePath string            `json:"docker_file_path"`
    BuildDirectory string            `json:"build_directory"`
    BuildArgs      map[string]string `json:"build_args"`
    WriteArtifacts []Artifact        `json:"write_artifacts"`
    ReadArtifacts  []Artifact        `json:"read_artifacts"`
}

// ReadManifest reads the manifest from a file
func ReadManifest(manifestFilePath string) (Manifest, error) {
    var manifest Manifest

    fileContents, err := ioutil.ReadFile(manifestFilePath)
    if err != nil {
        return manifest, err
    }

    err = json.Unmarshal(fileContents, &manifest)
    if err != nil {
        return manifest, err
    }

    return manifest, nil
}
