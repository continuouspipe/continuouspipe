package builder

import (
    "io"
    "regexp"
    "fmt"
    "github.com/docker/engine-api/types"
)

type RetryImagePusher struct {
    decoratedPusher ImagePusher
}

type RetryImageBuilder struct {
    decoratedBuilder ImageBuilder
}

func (rip *RetryImagePusher) Push(imageName string, authConfig string, output io.Writer) error {
    var err error
    numberOfCalls := 0

    for {
        numberOfCalls++
        if err = rip.decoratedPusher.Push(imageName, authConfig, output); err == nil {
            return err
        }

        if !shouldRetryAfterError(err) {
            return err
        }

        if numberOfCalls >= 3 {
            break
        }
    }

    return err
}

func (rib *RetryImageBuilder) Build(buildContext io.Reader, options types.ImageBuildOptions, output io.Writer) error {
    var err error
    numberOfCalls := 0

    for {
        numberOfCalls++
        if err = rib.decoratedBuilder.Build(buildContext, options, output); err == nil {
            return err
        }

        if !shouldRetryAfterError(err) {
            return err
        }

        if numberOfCalls >= 3 {
            break
        }
    }

    return err
}

func shouldRetryAfterError (err error) bool {
    errorString := err.Error()

    matchingRegexes := []string{
        "^([A-Za-z]{3,4}) ([a-z0-9:/.:-]+): EOF",
        "^([A-Za-z]{3,4}) ([a-z0-9:/.:-]+): ([A-Za-z0-9 /:.?&%=]+): i/o timeout",
        ": read tcp ([0-9.:]+): use of closed network connection",
        ": net/http: request canceled \\(Client.Timeout exceeded while awaiting headers\\)",
        "^use of closed network connection",
        "^push (or pull )?([^ ]+) is already in progress",
        "net/http: TLS handshake timeout",
        "(?i)^Received unexpected HTTP status: 500 Internal Server Error",
        "^error parsing HTTP 413 response body:",
        ": io: read/write on closed pipe$",
    }

    for _, matchingRegex := range matchingRegexes {
        matched, matchingError := regexp.MatchString(matchingRegex, errorString)

        if matchingError != nil {
            fmt.Println(matchingError)
        }

        if matched {
            return true
        }
    }

    return false
}
