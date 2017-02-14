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

type ClusterInfo struct {
	client *http.Client
}

func NewClusterInfo() *ClusterInfo {
	clusterInfo := &ClusterInfo{}
	clusterInfo.client = &http.Client{}
	return clusterInfo
}

type ApiTeam struct {
	Slug        string `json:"slug"`
	Name        string `json:"name"`
	BucketUuid  string      `json:"bucket_uuid"`
	Memberships []ApiMembership `json:"memberships"`
}

type ApiMembership struct {
	User       ApiUser `json:"user"`
	Permission ApiPermission `json:"permissions"`
}

type ApiUser struct {
	Username   string      `json:"username"`
	Email      string      `json:"email"`
	BucketUuid string      `json:"bucket_uuid"`
	Roles      []string      `json:"roles"`
}

type ApiPermission struct {
	Permission string `json:"permission"`
}

type ApiBucketClusters struct {
	Clusters []ApiCluster `json:"clusters"`
}

type ApiCluster struct {
	Identifier string    `json:"identifier"`
	Address    string    `json:"address"`
	Version    string    `json:"version"`
	Username   string    `json:"username"`
	Password   string    `json:"password"`
	Type       string    `json:"type"`
}

//return the url of the cluster e.g. https://100.200.300.400/
func (c ClusterInfo) GetClusterUrl(user string, apiKey string, teamName string, clusterIdentifier string) (*url.URL, error) {
	//bucketUuid, err := c.getBucketUuid(user, apiKey, teamName, clusterIdentifier)
	//if err != nil {
	//	return nil, err
	//}
	//if len(bucketUuid) == 0 {
	//	return nil, fmt.Errorf("user bucket id not found")
	//}

	//clusterAddress, err := c.getClusterAddress(bucketUuid, apiKey)
	//if err != nil {
	//	return nil, err
	//}
	//
	//url, err := url.Parse(clusterAddress)
	//if err != nil {
	//	return nil, err
	//}
	//
	//
	//return url, nil
}

func (c ClusterInfo) GetClusterBasicAuthInfo(cpUsername string, cpApiKey string, teamName string, clusterIdentifier string) (clusterUser string, clusterPassword string) {
	return "", ""
}

func (c ClusterInfo) GetApiTeams() {
	url := c.getAuthenticatorUrl()
	url.Path = "/api/teams/"
}

func (c ClusterInfo) GetApiBucketClusters(bucketUuid string, apiKey string) (*ApiBucketClusters, error) {
	url := c.getAuthenticatorUrl()
	url.Path = "/api/bucket/" + bucketUuid + "/clusters"

	req, err := http.NewRequest("GET", url.String(), nil)

	//TODO: Master key to be taken from config
	req.Header.Add("X-Api-Key", "")
	if err != nil {
		return nil, err
	}

	respBody, err := c.getResponseBody(c.client, req)
	if err != nil {
		return nil, err
	}

	bucketInfoResponse := &ApiBucketClusters{}
	err = json.Unmarshal(respBody, bucketInfoResponse)
	if err != nil {
		return nil, err
	}

	return bucketInfoResponse, nil
}

func (c ClusterInfo) GetApiUser(user string, apiKey string) (*ApiUser, error) {
	url := c.getAuthenticatorUrl()
	url.Path = "/api/user/" + user

	req, err := http.NewRequest("GET", url.String(), nil)
	req.Header.Add("X-Api-Key", apiKey)
	if err != nil {
		return nil, err
	}

	respBody, err := c.getResponseBody(c.client, req)
	if err != nil {
		return nil, err
	}

	apiUserResponse := &ApiUser{}
	err = json.Unmarshal(respBody, apiUserResponse)
	if err != nil {
		return nil, err
	}

	return apiUserResponse, nil
}

func (c ClusterInfo) getResponseBody(client *http.Client, req *http.Request) ([]byte, error) {
	res, err := client.Do(req)
	defer res.Body.Close()
	if err != nil {
		return nil, err
	}
	if res.StatusCode != http.StatusOK {
		return nil, fmt.Errorf("Error requesting user information, request status: %s", res.StatusCode)
	}
	resBody, err := ioutil.ReadAll(res.Body)
	if err != nil {
		return nil, err
	}
	return resBody, nil
}

func (c ClusterInfo) getAuthenticatorUrl() *url.URL {
	return &url.URL{
		Scheme: "https",
		Host:   "authenticator-staging.continuouspipe.io",
	}
}
