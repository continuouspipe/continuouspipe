package main

import (
    "golang.org/x/net/context"
    "cloud.google.com/go/pubsub"
    "flag"
    "log"
    "github.com/continuouspipe/continuouspipe/message-puller/puller"
    "os"
    "encoding/base64"
    "fmt"
    "io/ioutil"
    "google.golang.org/api/option"
    "time"
)

func main() {
    googleCloudProjectId := flag.String("google-project-id", "", "The Google Cloud project ID")
    googleServiceAccountFilePath := flag.String("service-account-file-path", "", "the path of the service account to use, if any")
    googleServiceAccount := flag.String("service-account", "", "the base64 encoded service account")
    subscriptionIdentifier := flag.String("subscription", "", "The subscription identifier")
    scriptPath := flag.String("script-path", "", "The path of the script to execute")
    flag.Parse()

    // Get service account from base64-encoded string
    if *googleServiceAccountFilePath == "" && *googleServiceAccount != "" {
        filePath, err := ServiceAccountFilePathFromBase64String(*googleServiceAccount)
        if err != nil {
            panic(err)
        }

        googleServiceAccountFilePath = &filePath
    }

    // Get PubSub client
    client, err := NewPubSubClient(*googleCloudProjectId, *googleServiceAccountFilePath)
    if err != nil {
        log.Fatal(err)
        return
    }

    handler := puller.NewAcknowledgeIfNoErrorHandler(
        puller.NewExecuteMessageHandler(
            puller.NewCommandExecuter(
                log.New(os.Stderr, "", log.Ldate | log.Ltime),
                log.New(os.Stdout, "", log.Ldate | log.Ltime),
            ),
            puller.NewCommandFactory(*scriptPath),
        ),
    )

    subscription := client.Subscription(*subscriptionIdentifier)
    subscription.ReceiveSettings.MaxExtension = 60 * time.Minute

    fmt.Println("Receiving messages...")
    err = subscription.Receive(context.Background(), func(ctx context.Context, msg *pubsub.Message) {
        handler.Handle(msg)
    })

    if (err != nil) {
        log.Fatal(err)
    }
}

func NewPubSubClient(googleCloudProjectId string, googleServiceAccountFilePath string) (*pubsub.Client, error) {
    options := []option.ClientOption{}
    if (googleServiceAccountFilePath != "") {
        options = append(options, option.WithServiceAccountFile(googleServiceAccountFilePath))
    }

    ctx := context.Background()

    return pubsub.NewClient(ctx, googleCloudProjectId, options...)
}

func ServiceAccountFilePathFromBase64String(base64EncodedServiceAccount string) (string, error) {
    file, err := ioutil.TempFile(os.TempDir(), "prefix")
    if err != nil {
        return "", err
    }

    decoded, err := base64.StdEncoding.DecodeString(base64EncodedServiceAccount)
    if err != nil {
        return "", fmt.Errorf("Base64 encoded service account seems invalid: %s", err.Error())
    }

    _, err = file.Write(decoded)
    if err != nil {
        return "",  fmt.Errorf("Can't write service accunt: %s", err.Error())
    }

    return file.Name(), nil
}

