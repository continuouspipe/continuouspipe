package cpapi

import (
	"net/url"
	"fmt"
	"net/http"
	"encoding/json"
	"io/ioutil"
	"github.com/spf13/viper"
)

type ClusterInfoProvider interface {
	GetClusterUrl(cpUsername string, cpApiKey string, teamName string, clusterIdentifier string) (*url.URL, error)
	GetClusterBasicAuthInfo(cpUsername string, cpApiKey string, teamName string, clusterIdentifier string) (clusterUser string, clusterPassword string, err error)
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
	Slug       string `json:"slug"`
	Name       string `json:"name"`
	BucketUuid string      `json:"bucket_uuid"`

	//Should be []ApiMembership although there is a bug on the api where a list of object with keys "1", "2" is returned
	//instead of being a json array
	Memberships []interface{} `json:"memberships"`
}

type ApiMembership struct {
	Team        ApiTeam `json:"team"`
	User        ApiUser `json:"user"`
	Permissions []string `json:"permissions"`
}

type ApiUser struct {
	Username   string      `json:"username"`
	Email      string      `json:"email"`
	BucketUuid string      `json:"bucket_uuid"`
	Roles      []string      `json:"roles"`
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
func (c ClusterInfo) GetClusterUrl(cpUsername string, cpApiKey string, teamName string, clusterIdentifier string) (*url.URL, error) {
	apiTeam, err := c.GetApiTeam(cpUsername, cpApiKey, teamName)
	if err != nil {
		return nil, err
	}

	clustersInfo, err := c.GetApiBucketClusters(apiTeam.BucketUuid)
	if err != nil {
		return nil, err
	}

	var clusterAddress string
	for _, cluster := range clustersInfo {
		if cluster.Identifier != clusterIdentifier {
			continue
		}
		clusterAddress = cluster.Address
	}

	clusterUrl, err := url.Parse(clusterAddress)
	if err != nil {
		return nil, err
	}

	return clusterUrl, nil
}

func (c ClusterInfo) GetClusterBasicAuthInfo(cpUsername string, cpApiKey string, teamName string, clusterIdentifier string) (clusterUser string, clusterPassword string, err error) {
	apiTeam, err := c.GetApiTeam(cpUsername, cpApiKey, teamName)
	if err != nil {
		return "", "", err
	}

	clustersInfo, err := c.GetApiBucketClusters(apiTeam.BucketUuid)
	if err != nil {
		return "", "", err
	}

	clusterUser = ""
	clusterPassword = ""
	for _, cluster := range clustersInfo {
		if cluster.Identifier != clusterIdentifier {
			continue
		}
		clusterUser = cluster.Username
		clusterPassword = cluster.Password
	}

	return clusterUser, clusterPassword, nil
}

func (c ClusterInfo) GetApiTeam(user string, apiKey string, teamName string) (*ApiTeam, error) {
	url := c.getAuthenticatorUrl()
	url.Path = "/api/teams/" + teamName

	req, err := http.NewRequest("GET", url.String(), nil)
	req.Header.Add("X-Api-Key", apiKey)
	if err != nil {
		return nil, err
	}

	respBody, err := c.getResponseBody(c.client, req)

	if err != nil {
		return nil, err
	}

	apiTeamsResponse := &ApiTeam{}
	err = json.Unmarshal(respBody, apiTeamsResponse)
	if err != nil {
		return nil, err
	}

	return apiTeamsResponse, nil
}

//Use the master api key to get the details of the cluster, including the auth password for kubernetes in cleartext
func (c ClusterInfo) GetApiBucketClusters(bucketUuid string) ([]ApiCluster, error) {
	url := c.getAuthenticatorUrl()
	url.Path = "/api/bucket/" + bucketUuid + "/clusters"

	req, err := http.NewRequest("GET", url.String(), nil)

	req.Header.Add("X-Api-Key", viper.GetString("master-api-key"))
	if err != nil {
		return nil, err
	}

	respBody, err := c.getResponseBody(c.client, req)
	if err != nil {
		return nil, err
	}

	clusters := make([]ApiCluster, 0)
	err = json.Unmarshal(respBody, &clusters)
	if err != nil {
		return nil, err
	}

	return clusters, nil
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
