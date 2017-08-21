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

    /*
    data, err := ioutil.ReadFile(*googleServiceAccountFilePath)
    if err != nil {
        log.Fatal(err)
    }

    //conf, err := google.JWTConfigFromJSON(data, "https://www.googleapis.com/auth/pubsub")
    if err != nil {
        log.Fatal(err)
    }
    */
    ctx := context.Background()
    //ts := conf.TokenSource(ctx)
    client, err := pubsub.NewClient(ctx, *googleCloudProjectId)//, option.WithTokenSource(ts))
    //if err != nil {
    //    log.Fatal("new client:", err)
    //}

    //ctx := context.Background()
    //client, err := pubsub.NewClient(ctx, *googleCloudProjectId, option.WithServiceAccountFile(*googleServiceAccountFilePath))
    if err != nil {
        log.Fatal(err)
        return
    }

    subscription := client.Subscription(*subscriptionIdentifier)
    fmt.Println("Receiving messages...")
    err = subscription.Receive(ctx, func(ctx context.Context, msg *pubsub.Message) {
        executer := puller.NewCommandExecuter(
            log.New(os.Stderr, "", log.Ldate|log.Ltime),
            log.New(os.Stdout, "", log.Ldate|log.Ltime),
        )

        body := base64.StdEncoding.EncodeToString(msg.Data)
        cmd := puller.NewCommandFactory(*scriptPath).Create(body)

        result := executer.Execute(cmd, true)
        var err error = nil
        if 0 != result {
            err = fmt.Errorf("Command finished with status %d", result)
        }

        // If no error, we acknowledge the message. If not, then we simply do not acknowledge it,
        // log the exception and it will be re-queued later.
        if err == nil {
            msg.Ack()
        }
    })

    if (err != nil) {
        log.Fatal(err)
    }
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
