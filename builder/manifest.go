package builder

import (
    "github.com/docker/engine-api/types"
    "io/ioutil"
    "encoding/json"
)

// Manifest represents the manifest file used to configure the build
type Manifest struct {
    AuthConfigs       map[string]types.AuthConfig `json:"auth_configs"`
    LogBoundary       string                      `json:"log_boundary"`
    ArtifactsBucket   string                      `json:"artifacts_bucket"`
    Steps             []ManifestStep              `json:"steps"`
    FirebaseParentLog string                      `json:"firebase_parent_log"`
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
