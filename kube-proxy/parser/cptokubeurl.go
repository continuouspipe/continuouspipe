package parser

import (
	"fmt"
	"strings"
)

type CpToKubeUrlParser interface {
	ExtractFlowId(string) (string, error)
	ExtractClusterId(string) (string, error)
	RemoveCpDataFromUri(string) (string, error)
}

type CpToKubeUrl struct{}

func NewCpToKubeUrl() *CpToKubeUrl {
	return &CpToKubeUrl{}
}

func (p CpToKubeUrl) ExtractFlowId(urlPath string) (string, error) {
	pathSections := strings.Split(urlPath, "/")
	if len(pathSections) < 3 {
		return "", fmt.Errorf("invalid url path lenght of %d", len(pathSections))
	}
	return pathSections[1], nil
}

func (p CpToKubeUrl) ExtractClusterId(urlPath string) (string, error) {
	pathSections := strings.Split(urlPath, "/")
	if len(pathSections) < 3 {
		return "", fmt.Errorf("invalid url path lenght of %d", len(pathSections))
	}
	return pathSections[2], nil
}

func (p CpToKubeUrl) RemoveCpDataFromUri(urlPath string) (string, error) {
	pathSections := strings.Split(urlPath, "/")
	if len(pathSections) < 3 {
		return "", fmt.Errorf("invalid url path lenght of %d", len(pathSections))
	}
	kubeUrl := pathSections[3:]
	return strings.Join(kubeUrl, "/"), nil
}
