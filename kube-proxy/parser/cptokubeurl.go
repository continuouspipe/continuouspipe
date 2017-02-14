package parser

import (
	"strings"
)

type CpToKubeUrlParser interface {
	ExtractTeamName(urlPath string) string
	ExtractClusterId(urlPath string) string
	RemoveCpDataFromRawUrl(url string) string
}

type CpToKubeUrl struct{}

func NewCpToKubeUrl() *CpToKubeUrl {
	return &CpToKubeUrl{}
}

func (p CpToKubeUrl) ExtractTeamName(urlPath string) string {
	pathSections := strings.Split(urlPath, "/")
	return pathSections[1]
}

func (p CpToKubeUrl) ExtractClusterId(urlPath string) string {
	pathSections := strings.Split(urlPath, "/")
	return pathSections[2]
}

func (p CpToKubeUrl) RemoveCpDataFromRawUrl(url string) string {
	toRemove := p.ExtractTeamName(url) + "/" + p.ExtractClusterId(url) + "/"
	return strings.Replace(url, toRemove, "", 1)
}
