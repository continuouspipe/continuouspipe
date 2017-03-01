//logs data on keen.io
package keenapi

import (
	"bufio"
	"bytes"
	"encoding/json"
	"fmt"
	"github.com/continuouspipe/kube-proxy/cplogs"
	"net/http"
	"os"
)

var envKeenIoProjectId, _ = os.LookupEnv("KEEN_IO_PROJECT_ID")
var envKeenIoEventCollection, _ = os.LookupEnv("KEEN_IO_EVENT_COLLECTION")
var envKeenIoWriteKey, _ = os.LookupEnv("KEEN_IO_WRITE_KEY")

type KeenApiPayload struct {
	Url         string `json:"url"`
	StartTime   string `json:"start_time"`
	EndTime     string `json:"end_time"`
	Duration    string `json:"duration"`
	Description string `json:"description"`
}

type Sender struct {
	ProjectId, EventCollection, WriteKey string
}

func NewSender() *Sender {
	s := &Sender{}
	s.ProjectId = envKeenIoProjectId
	s.EventCollection = envKeenIoEventCollection
	s.WriteKey = envKeenIoWriteKey
	return s
}

func (k *Sender) Send(payload interface{}) (bool, error) {
	//do not send if the required information is missing
	if k.WriteKey == "" || k.ProjectId == "" || k.EventCollection == "" {
		return false, nil
	}

	out, err := json.Marshal(payload)
	if err != nil {
		return false, err
	}

	reader := bytes.NewReader(out)

	req, err := http.NewRequest("POST", k.getEndpointUrl(), reader)
	if err != nil {
		cplogs.V(4).Infof("could not create request for GET request for url: %s", k.getEndpointUrl())
		return false, err
	}

	req.Header.Set("Content-Type", "application/json")

	client := http.Client{}
	resp, err := client.Do(req)
	if err != nil {
		cplogs.V(4).Infof("could not execute the GET request for url: %s", k.getEndpointUrl())
		return false, err
	}
	if resp.StatusCode == 200 || resp.StatusCode == 201 {
		return true, nil
	}

	respBody := ""
	scanner := bufio.NewScanner(resp.Body)
	for scanner.Scan() {
		respBody = respBody + "\n" + scanner.Text()
	}

	err = fmt.Errorf("we didn't receive the expected status code OK or Create from keen.io. Status code %d, body %s", resp.StatusCode, respBody)
	return false, err
}

func (k *Sender) getEndpointUrl() string {
	return fmt.Sprintf("https://api.keen.io/3.0/projects/%s/events/%s?api_key=%s",
		k.ProjectId,
		k.EventCollection,
		k.WriteKey)
}
