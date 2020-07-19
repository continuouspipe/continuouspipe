package cpapi

import (
	"encoding/json"
	"fmt"
	"io/ioutil"
	"net/http"
	"net/url"
	"os"
	"github.com/golang/glog"
)

var envCpApiUrl, _ = os.LookupEnv("KUBE_PROXY_API_URL") //e.g.: https://api
//var envCpRiverHost, _ = os.LookupEnv("KUBE_PROXY_RIVER_HOST")                 //e.g.: your-river.example.com
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

type ApiFlow struct {
	Configuration interface{}   `json:"configuration"`
	Pipelines     []interface{} `json:"pipelines"`
	Repository    interface{}   `json:"repository"`
	Team          ApiFlowTeam   `json:"team"`
	User          interface{}   `json:"user"`
	Uuid          string        `json:"uuid"`
}

type ApiFlowTeam struct {
	BucketUuid string `json:"bucket_uuid"`
	Slug       string `json:"slug"`
	Name       string `json:"name"`
}

type ApiClusterCredentials struct {
	Username   string `json:"username"`
	Password   string `json:"password"`
	GoogleServiceAccount string `json:"google_cloud_service_account"`
}

type ApiCluster struct {
	Identifier string `json:"identifier"`
	Address    string `json:"address"`
	Version    string `json:"version"`
	Type       string `json:"type"`
	Credentials ApiClusterCredentials `json:"credentials"`
}


func (c ClusterInfo) GetCluster(cpUsername string, apiKeyOrToken string, flowId string, clusterIdentifier string) (*ApiCluster, error) {
	apiFlow, err := c.GetApiFlow(cpUsername, apiKeyOrToken, flowId)
	if err != nil {
		return nil, fmt.Errorf("Failed to get the api flow, " + err.Error())
	}

	clustersInfo, err := c.GetApiBucketClusters(apiFlow.Team.BucketUuid)
	if err != nil {
		return nil, fmt.Errorf("Failed to get the api bucket clusters, " + err.Error())
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

func (c ClusterInfo) GetApiFlow(cpUsername string, apiKeyOrToken string, flowId string) (*ApiFlow, error) {
	url, err := c.getApiUrl()
	if err != nil {
	    return nil, err
	}

	url.Path = "/flows/" + flowId

	req, err := http.NewRequest("GET", url.String(), nil)
	if err != nil {
		return nil, fmt.Errorf("Failed to create new request for GetApiFlow, " + err.Error())
	}

	if cpUsername == "x-token-auth" {
		req.Header.Add("Authorization", "Bearer "+apiKeyOrToken)
	} else {
		req.Header.Add("X-Api-Key", apiKeyOrToken)
	}

	respBody, err := c.getResponseBody(c.client, req)

	if err != nil {
		glog.V(4).Infof("Error during GetApiFlow request %s response %s, "+err.Error(), url.String(), respBody)
		glog.Flush()
		return nil, fmt.Errorf("Failed to get response body for GetApiFlow, " + err.Error())
	}

	apiFlowResponse := &ApiFlow{}
	err = json.Unmarshal(respBody, apiFlowResponse)
	if err != nil {
		glog.V(4).Infof("Error unmarshalling GetApiFlow request %s response %s, "+err.Error(), url.String(), respBody)
		glog.Flush()
		return nil, err
	}

	return apiFlowResponse, nil
}

//Use the master api key to get the details of the cluster, including the auth password for kubernetes in cleartext
func (c ClusterInfo) GetApiBucketClusters(bucketUuid string) ([]ApiCluster, error) {
	url, err := c.getApiUrl()
	if err != nil {
	    return nil, err
	}

	url.Path = "/bucket/" + bucketUuid + "/clusters"

	req, err := http.NewRequest("GET", url.String(), nil)

	req.Header.Add("X-Api-Key", envCpMasterApiKey)
	if err != nil {
		return nil, fmt.Errorf("Failed to create new request for GetApiBucketClusters, " + err.Error())
	}

	respBody, err := c.getResponseBody(c.client, req)
	if err != nil {
		glog.V(4).Infof("Error during GetApiBucketClusters request %s response %s, "+err.Error(), url.String(), respBody)
		glog.Flush()
		return nil, err
	}

	clusters := make([]ApiCluster, 0)
	err = json.Unmarshal(respBody, &clusters)
	if err != nil {
		glog.V(4).Infof("Error unmarshalling GetApiFlow request %s response %s, "+err.Error(), url.String(), respBody)
		glog.Flush()
		return nil, err
	}

	return clusters, nil
}

func (c ClusterInfo) getResponseBody(client *http.Client, req *http.Request) ([]byte, error) {
	res, err := client.Do(req)
	if err != nil {
		glog.V(4).Infoln("Error when creating client for request")
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

func (c ClusterInfo) getApiUrl() (*url.URL, error) {
    return url.Parse(envCpApiUrl)
}
