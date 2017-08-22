package builder

import (
    "io"
    "bytes"
    "testing"
    "errors"
    "github.com/docker/engine-api/types"
    "strings"
)

type PushCallback func(output io.Writer) error
type PredictableImagePusher struct {
    callback PushCallback
}
type PredictableImageBuilder struct {
    callback PushCallback
}

type PushCallbackFactory struct {
    error error
    message   string

    callThreshold int
    afterThresholdError error

    callCount int
}

func (pcf *PushCallbackFactory) callback(output io.Writer) error {
    pcf.callCount++

    if pcf.message == "" {
        pcf.message = "PUSH"
    }

    output.Write([]byte(pcf.message))

    if (0 != pcf.callThreshold && pcf.callCount > pcf.callThreshold) {
        return pcf.afterThresholdError
    }

    return pcf.error
}

func (pip *PredictableImagePusher) Push(imageName string, authConfig string, output io.Writer) error {
    return pip.callback(output)
}

func (pib *PredictableImageBuilder) Build(buildContext io.Reader, options types.ImageBuildOptions, output io.Writer) error {
    return pib.callback(output)
}

func TestItDoNotRetryWhenPushWorks(t *testing.T) {
    pusher := NewRetryImagePush((&PushCallbackFactory{error: nil}).callback)

    var b bytes.Buffer
    err := pusher.Push("foo", "bar", &b)

    if err != nil {
        t.Errorf("Got an error: %s", err.Error())
    }

    if (b.String() != "PUSH") {
        t.Errorf("Got %s instead of PUSH", b.String())
    }
}

func TestItDoNotRetryWhenANormalFailureIsHappening(t *testing.T) {
    pusher := NewRetryImagePush((&PushCallbackFactory{error: errors.New("Permission denied for image: quay.io/continuouspipe/builder")}).callback)

    var b bytes.Buffer
    err := pusher.Push("foo", "bar", &b)

    if err == nil {
        t.Error("Should have returned error, returned nil")
    }
    if (b.String() != "PUSH") {
        t.Errorf("Got %s instead of PUSH", b.String())
    }
}

func TestItRetriesNetworkErrors(t *testing.T) {
    errorStrings := []string{
        "Head https://registry-1.docker.io/v2/inviqasession/graze-mysql/blobs/sha256:4280dc1f38b4454b2c13ecc2164e020f3e57f0bb6f2611ed2b9c2bef16d60cef: EOF",
        "Head https://registry-1.docker.io/v2/inviqasession/graze/blobs/sha256:a3ed95caeb02ffe68cdd9fd84406680ae93d633cb16422d00e8a7c22955b46d4: dial tcp 54.152.156.80:443: i/o timeout",
        "Failed to upload metadata: Put https://cdn-registry-1.docker.io/v1/images/81f2929c91fd50473aef0f72dcc507206c25b2d4673c155e7e14e00d8dc59245/json: net/http: TLS handshake timeout",
        "Head https://dseasb33srnrn.cloudfront.net/registry-v2/docker/registry/v2/blobs/sha256/5c/5c1d75783f7f66ae3006b86d2a3868a482699942c9e4b8951c3c3fc282ec5490/data?Expires=1462800791&Signature=LK72D8I7GtxTxD3HsZ5tOiCdGYCq5aypMaDqBLAFmgtP94UOQcQN5D9TBTAFepffJ4gAjEu3Kd-VqkavCbic274gg95D9irsbYgCzqEdmXvhNhPh6F3OP8fJ-WrQ0uCFQ~GKHSDbjcckUM0W68UsBmYSakQlPDQA--cN8GBOq3U_&Key-Pair-Id=APKAJECH5M7VWIS5YZ6Q: net/http: TLS handshake timeout",
        "Received unexpected HTTP status: 500 Internal Server Error",
        "Received unexpected HTTP status: 500 INTERNAL SERVER ERROR",
        "received unexpected HTTP status: 502 Bad Gateway",
        "Head https://quay.io/v2/sroze/ft/blobs/sha256:c4d7cdda3413170869ccb4c6803b666efd97cd83f609813291df37cd93a153f1: Get https://quay.io/v2/auth?account=sroze&scope=repository%3Asroze%2Fft%3Apush%2Cpull&service=quay.io: net/http: request canceled (Client.Timeout exceeded while awaiting headers)",
        "Put https://quay.io/v2/sroze/ft/manifests/51c2b6f8de95a36a49813a1bbdabd2d2a7d07e9f: Get https://quay.io/v2/auth?account=sroze&scope=repository%3Asroze%2Fft%3Apush%2Cpull&service=quay.io: net/http: request canceled (Client.Timeout exceeded while awaiting headers)",
        "Put https://quay.io/v2/sroze/ft/blobs/uploads/b8f93d65-296c-41ed-9324-3369f69112ee?digest=sha256%3A200140c720609a98e8da53eb9596b732545d9085bfe583dcbd7a5b0503b3415f: Get https://quay.io/v2/auth?account=sroze&scope=repository%3Asroze%2Fft%3Apush%2Cpull&service=quay.io: read tcp 54.235.117.86:443: use of closed network connection",
        "Get https://quay.io/v2/auth?account=sroze&scope=repository%3Asroze%2Fft%3Apush%2Cpull&service=quay.io: net/http: request canceled (Client.Timeout exceeded while awaiting headers)",
        "Get https://quay.io/v2/continuouspipe/magento2-nginx-php7/manifests/v1.0: Get https://quay.io/v2/auth?scope=repository%3Acontinuouspipe%2Fmagento2-nginx-php7%3Apull&service=quay.io: net/http: request canceled (Client.Timeout exceeded while awaiting headers)",
        "Head https://quay.io/v2/inviqa_images/ft/blobs/sha256:83aa9bf0098d040f87211c18b37a63bffd79ddfcb75b6ec2549ccee4a69bd72a: net/http: TLS handshake timeout [1]",
        "error parsing HTTP 413 response body: invalid character '&lt;' looking for beginning of value: \"\r\n413 Request Entity Too Large\r\n\r\n<center><h1>413 Request Entity Too Large</h1></center>\r\n<hr><center>nginx/1.13.3</center>\r\n\r\n\r\n\" [1]</span>",
    }

    for _, errorString := range errorStrings {
        pusher := NewRetryImagePush((&PushCallbackFactory{
            error: errors.New(errorString),
            callThreshold: 1,
            afterThresholdError: nil,
        }).callback)

        var b bytes.Buffer
        err := pusher.Push("image", "auth", &b)

        if err != nil {
            t.Errorf("Got an error: %s", err.Error())
        }

        if (b.String() != "PUSHPUSH") {
            t.Errorf("Got %s instead of PUSHPUSH", b.String())
        }
    }
}

func TestItRetriesAMaximumNumberOfTimes(t *testing.T) {
    pusher := NewRetryImagePush((&PushCallbackFactory{
        error: errors.New("Head https://quay.io/v2/inviqa_images/ft/blobs/sha256:83aa9bf0098d040f87211c18b37a63bffd79ddfcb75b6ec2549ccee4a69bd72a: net/http: TLS handshake timeout [1]"),
        callThreshold: 5,
        afterThresholdError: nil,
    }).callback)

    var b bytes.Buffer
    err := pusher.Push("foo", "bar", &b)

    if err == nil {
        t.Error("Should have returned error, returned nil")
    }
    if (b.String() != "PUSHPUSHPUSH") {
        t.Errorf("Got %s instead of PUSHPUSHPUSH", b.String())
    }
}

func TestItRetriesWhenBuildingFailsWithSpecificExceptions(t *testing.T) {
    errorStrings := []string{
        "Get https://quay.io/v2/continuouspipe/symfony-php7.1-apache/manifests/latest: net/http: TLS handshake timeout [1]",
        "An error occurred trying to connect: Post http://%2Fvar%2Frun%2Fdocker.sock/v1.23/build?buildargs=%7B%22BUILD_APPLICATION_ENV%22%3A%22development-continuouspipe%22%2C%22BUILD_DEVELOPMENT_MODE%22%3A%22false%22%7D&cgroupparent=&cpuperiod=0&cpuquota=0&cpusetcpus=&cpusetmems=&cpushares=0&dockerfile=.%2FDockerfile&labels=null&memory=0&memswap=0&rm=0&shmsize=0&t=quay.io%2Finviqa_images%2Feigensonne%3A83ea3dccdddf0b9656684c9f1edcb5ce24a48a9a&ulimits=null: io: read/write on closed pipe",
    }

    for _, errorString := range errorStrings {
        builder := NewRetryImageBuilder((&PushCallbackFactory{
            error: errors.New(errorString),
            callThreshold: 1,
            afterThresholdError: nil,
            message: "BUILD",
        }).callback)

        var reader = strings.NewReader("foo")
        var b bytes.Buffer
        err := builder.Build(reader, types.ImageBuildOptions{}, &b)

        if err != nil {
            t.Errorf("Should not have returned error, returned: %s", err.Error())
        }
        if (b.String() != "BUILDBUILD") {
            t.Errorf("Got %s instead of BUILDBUILD", b.String())
        }
    }
}

func TestItDoNotRetryWithANormalErrorFromBuilds(t *testing.T) {
    builder := NewRetryImageBuilder((&PushCallbackFactory{
        error: errors.New("Command container build returned status 1"),
        message: "BUILD",
    }).callback)

    var reader = strings.NewReader("foo")
    var b bytes.Buffer
    err := builder.Build(reader, types.ImageBuildOptions{}, &b)

    if err == nil {
        t.Error("Should have returned error, returned nil")
    }
    if (b.String() != "BUILD") {
        t.Errorf("Got %s instead of BUILD", b.String())
    }
}

func NewRetryImagePush(callback PushCallback) RetryImagePusher {
    return RetryImagePusher{
        decoratedPusher: &PredictableImagePusher{
            callback: callback,
        },
    }
}

func NewRetryImageBuilder(callback PushCallback) RetryImageBuilder {
    return RetryImageBuilder{
        decoratedBuilder: &PredictableImageBuilder{
            callback: callback,
        },
    }
}
