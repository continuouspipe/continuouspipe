package builder

import (
    "github.com/docker/engine-api/types"
    "io/ioutil"
    "encoding/json"
)

// Manifest represents the manifest file used to configure the build
type Manifest struct {
    AuthConfigs    map[string]types.AuthConfig `json:"auth_configs"`
    DockerfilePath string                      `json:"docker_file_path"`
    BuildArgs      map[string]string           `json:"build_args"`
    Name           string                      `json:"name"`
    LogBoundary    string                      `json:"log_boundary"`
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
