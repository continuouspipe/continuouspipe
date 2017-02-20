package cpapi

import (
	"net/url"
	"fmt"
	"net/http"
	"encoding/json"
	"io/ioutil"
	"os"
	"github.com/continuouspipe/kube-proxy/cplogs"
)

var envCpAuthenticatorHost, _ = os.LookupEnv("KUBE_PROXY_AUTHENTICATOR_HOST") //e.g.: authenticator-staging.continuouspipe.io
var envCpMasterApiKey, _ = os.LookupEnv("KUBE_PROXY_MASTER_API_KEY")          // master api key for cp api

type ClusterInfoProvider interface {
	GetCluster(cpUsername string, cpApiKey string, teamName string, clusterIdentifier string) (*ApiCluster, error)
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

func (c ClusterInfo) GetCluster(cpUsername string, cpApiKey string, teamName string, clusterIdentifier string) (*ApiCluster, error) {
	apiTeam, err := c.GetApiTeam(cpUsername, cpApiKey, teamName)
	if err != nil {
		return nil, err
	}

	clustersInfo, err := c.GetApiBucketClusters(apiTeam.BucketUuid)
	if err != nil {
		return nil, err
	}

	var targetCluster ApiCluster
	for _, cluster := range clustersInfo {
		if cluster.Identifier != clusterIdentifier {
			continue
		}
		targetCluster = cluster
	}

	return &targetCluster, nil
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
		cplogs.V(4).Infof("Error during request %s response %s, error %s\n", url.String(), respBody, err.Error())
		cplogs.Flush()
		return nil, err
	}

	apiTeamsResponse := &ApiTeam{}
	err = json.Unmarshal(respBody, apiTeamsResponse)
	if err != nil {
		cplogs.V(4).Infof("Error unmarshalling request %s response %s, error %s\n", url.String(), respBody, err.Error())
		cplogs.Flush()
		return nil, err
	}

	return apiTeamsResponse, nil
}

//Use the master api key to get the details of the cluster, including the auth password for kubernetes in cleartext
func (c ClusterInfo) GetApiBucketClusters(bucketUuid string) ([]ApiCluster, error) {
	url := c.getAuthenticatorUrl()
	url.Path = "/api/bucket/" + bucketUuid + "/clusters"

	req, err := http.NewRequest("GET", url.String(), nil)

	req.Header.Add("X-Api-Key", envCpMasterApiKey)
	if err != nil {
		return nil, err
	}

	respBody, err := c.getResponseBody(c.client, req)
	if err != nil {
		cplogs.V(4).Infof("Error during request %s response %s, error %s\n", url.String(), respBody, err.Error())
		cplogs.Flush()
		return nil, err
	}

	clusters := make([]ApiCluster, 0)
	err = json.Unmarshal(respBody, &clusters)
	if err != nil {
		cplogs.V(4).Infof("Error unmarshalling request %s response %s, error %s\n", url.String(), respBody, err.Error())
		cplogs.Flush()
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
		cplogs.V(4).Infof("Error during request %s response %s, error %s\n", url.String(), respBody, err.Error())
		cplogs.Flush()
		return nil, err
	}

	apiUserResponse := &ApiUser{}
	err = json.Unmarshal(respBody, apiUserResponse)
	if err != nil {
		cplogs.V(4).Infof("Error unmarshalling request %s response %s, error %s\n", url.String(), respBody, err.Error())
		cplogs.Flush()
		return nil, err
	}

	return apiUserResponse, nil
}

func (c ClusterInfo) getResponseBody(client *http.Client, req *http.Request) ([]byte, error) {
	res, err := client.Do(req)
	if err != nil {
		cplogs.V(4).Infoln("Error when creating client for request")
		return nil, err
	}
	if res.Body == nil {
		return nil, fmt.Errorf("Error requesting user information, response body empty, request status: %d", res.StatusCode)
	}
	defer res.Body.Close()
	if res.StatusCode != http.StatusOK {
		return nil, fmt.Errorf("Error requesting user information, request status: %d", res.StatusCode)
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
		Host:   envCpAuthenticatorHost,
	}
}
