package cpapi

import (
	"net/url"
	"fmt"
	"net/http"
	"encoding/json"
	"io/ioutil"
)

type ClusterInfoProvider interface {
	GetClusterUrl(user string, apiKey string, teamName string, clusterIdentifier string) (*url.URL, error)
	GetClusterBasicAuthInfo(cpUsername string, cpApiKey string, teamName string, clusterIdentifier string) (clusterUser string, clusterPassword string)
}

type ClusterInfo struct{}

func NewClusterInfo() *ClusterInfo {
	return &ClusterInfo{}
}

type ApiUserResponse struct {
	Username   string      `json:"username"`
	Email      string      `json:"email"`
	BucketUuid string      `json:"bucket_uuid"`
	Roles      []string      `json:"roles"`
}

//return the url of the cluster e.g. https://100.200.300.400/
func (c ClusterInfo) GetClusterUrl(user string, apiKey string, teamName string, clusterIdentifier string) (*url.URL, error) {
	//get the user bucket using his apiKye
	url := &url.URL{
		Scheme: "https",
		Host:   "authenticator-staging.continuouspipe.io",
		Path:   "/api/user/" + user,
	}

	client := &http.Client{}
	req, err := http.NewRequest("GET", url.String(), nil)
	req.Header.Add("X-Api-Key", apiKey)
	if err != nil {
		return nil, err
	}
	res, err := client.Do(req)
	defer res.Body.Close()
	if err != nil {
		return nil, err
	}

	resBody, err := ioutil.ReadAll(res.Body)

	apiUserResponse := ApiUserResponse{}
	json.Unmarshal(resBody, &apiUserResponse)

	fmt.Println(apiUserResponse.BucketUuid)

	panic("die")

	//get the cluster info using the master key
	//https://authenticator-staging.continuouspipe.io/api/bucket/a0b40c68-eedc-11e6-97e2-0a580a840065/clusters?username=alessandrozucca&email=alessandro.zucca%40gmail.com

	return nil, nil
}

func (c ClusterInfo) GetClusterBasicAuthInfo(cpUsername string, cpApiKey string, teamName string, clusterIdentifier string) (clusterUser string, clusterPassword string) {
	return "", ""
}
