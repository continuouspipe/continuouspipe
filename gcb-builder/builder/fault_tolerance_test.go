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
        "An error occurred trying to connect: Post http://%2Fvar%2Frun%2Fdocker.sock/v1.23/build?buildargs=%7B%22ASSETS_CLEANUP%22%3A%22false%22%2C%22ASSETS_ENV%22%3A%22backup%22%2C%22ASSETS_FILES_ENABLED%22%3A%22true%22%2C%22ASSETS_PATH%22%3A%22%2Ftmp%22%2C%22ASSETS_S3_BUCKET%22%3A%22inviqa-assets-polo%22%2C%22AWS_ACCESS_KEY_ID%22%3A%22AKIAI5PS24AUQ5AAFW7Q%22%2C%22AWS_SECRET_ACCESS_KEY%22%3A%225pkvMRPm%2BkiGIzYxKF9T4EDTxqAEP08EH%2FoJYfNB%22%2C%22BUILD_USER_SSH_KNOWN_HOSTS%22%3A%22fDF8czhwZ0FwaC85TGQzU21GMHFsYlR0Tkg4MzlZPXxUMjZ3RzFNdjRLWGVPR25Ea3Y3OW05SjhWZUU9IHNzaC1yc2EgQUFBQUIzTnphQzF5YzJFQUFBQURBUUFCQUFBQkFRQzI5QjBWd3BFNitlU1pIRitZUHRUK1JveThvR1d6WEtmMnVLUVdlR0N3bE9pbzhNOVQyaEtnbndjT2wrVnh3KzExZ2hRYzRpelBHWThDckRrTm5aZVZkNDNLYmJNUEQySFlDdEJ1UUNWOFFuT2xhQjhJcWdjblZsREg5eitDL3RwTHlWNlBRekVQR1FydVFCTWp3SllXNDF6Mk1oUWlqSVJNNDNmeUpzbnNidFdiZFh4YkZTNEcwbnZLZUd0RVMxMUMvMG1xTGxpaVJFRzRjamlYd2tJaEUvVFRpYnZ6R1daQ0h4N3JuNGN2c0d3ZmQ4VGpFVURNaEN3SEluTE0wK0dTaVJCa3BOUmNNRXJmemlCOGFOL2dkUE9rK3djWjFPSVBjWC9MRk1IUzJ2WXh2VDB3ZjZLM3hBWFlobURScnRrRXBURVFVNzFERisyYnNpOURtTEg1CnwxfFA3UnlLWTIwM2Q2TjR1QUZiNGFXeExqcDdBaz18M2RFNmZ1TlVJY0NQKzFWbXdUTXgxZDZNSjY4PSBlY2RzYS1zaGEyLW5pc3RwMjU2IEFBQUFFMlZqWkhOaExYTm9ZVEl0Ym1semRIQXlOVFlBQUFBSWJtbHpkSEF5TlRZQUFBQkJCTkVraE4zRzZHZHN4REdEOGJZRUIwYnhhVVdyZmllck1wSG9MZ1dId3pJdWcvSWhscEpCb1MxTUpEL1pvRUNDUi8rS1l0SGZQaU1sQWlnYVcxUjRJR1E9Cg%3D%3D%22%2C%22BUILD_USER_SSH_PRIVATE_KEY%22%3A%22LS0tLS1CRUdJTiBSU0EgUFJJVkFURSBLRVktLS0tLQpNSUlKS0FJQkFBS0NBZ0VBbzJIdklaUmpJam8zRlJodEtPMUVjSStMajdZU0w2emM2UVBBTm1ISkxBdEpBNHMxCkk0YzJXZUNKaFFseENHN3B1UlhQZ29BYUZXbmpRYUg0VVFhelN6TkF4VHlpc1cvY1ZuRDluK3A3b2FqYkYrWVYKY0VTR0NkRU9wZ25hSmhHMXpDM0lVMlF3eTFVS1BlMWlHS0hPeDVmT3hEekxXTE9NblhxckVFUEdyTTY0WHhFVQpHekdPN1B4eE5acWF0UUN3S01pRnRudkZCLzdTQ1BPaTFmbHBFY2ttYlB6bmN1Uzh2SWgwRFZsK2YzeWVwd0pJCkRmL25jbUFrNm5Nd29mTXpVUDJ4Uk1YUDJndmd2THZuRGkwSklkcHJlZGJJTWJaTnFnTEZjSU1TL1A4RHk4dzQKdlNlSWFURXV3UlVzTkd2VTVmY3h1cWEwVWxkQTU5N0pWMSt1ZzYwckVnamlrbm5pNmZMSHhVUU5nOUM1U1Z3VgpPN2NYeGMrOHoyTEx4aDhJMUUrRzJnNkoxYzVqeG1nc0ZPeHQrN0xVNGtDMkxxRGpxcXBXMktsM1lxcGtIb1VCCndnM3dHWjNOenhpYTlBZ1Y4T1JUR1YwVTBVbmRmSGRzSkIxN1pwZlZnaWNLeURQbUtkcUEvUDhRNjZLMEl0eFQKcEUxS3FhQUZldkRnN3JEeDJXcHFBc2c5WmJwT1dXTW5hY1grNG9yRVVEMWh3RWlPekdCVkdwNGppNlArQ1RsRApHRkdCcG1ERE1YVDJxNnBvK2VZMjUxRFZ5dHAxZ3c5d3BvdnVVZUh0ODFhcEFCeCt1bTFObndPcnlEUkowVDNsClllMjE1UlZ2RnF2cWg4UTFoTUVQN0MvNzR1dldzVjE3UzRNTm43d0FRMDJ2N2xiUE95Z08zSDlZMHdFQ0F3RUEKQVFLQ0FnQUtvS2swYnZtUDFXZVYyTFBwRUozK0VHaE1uZFJMQTV5UEQ2NW1HekJCekM0Z3ErSjJBQ3FaNkJBcAo5alpERFN2bzNURlZWTTNkakxpNm9UV00xN203NzRhTURlaXJVQkp1RVFWK2JIdEVSYjZTckdYQ21zSjVTdjcvCjcrNGZ3ZEVvaWVYWS90QnB1WXRrQWRmNnNEUEtLZWJLUHdZZksvYSttNmNhOUwyc3FmbTJQVjhvY2EzUlhvNWkKcFQ3Zy9UMTRPYXJ3OXRZWE1nMHBoZWVXc1pXVEVabU5SMW9xMEJReTAxRnhPTndRb09PVkM0SHlYeTRIODVjVApKUkVKeDg4VHVwellVMkcwMWtiMUgrZmt0U3M5NTM1TU81dGtHRzZ1MjJWS2IweEI1cml4ZmdSRzBaWisrVE1JClhqODAzYnJJaHE3V2JGZVliNUtLUXFTb0tWSXBwZk8xalZaYU53TlIxL3paQTZuZW95c1FZY0dmNFZzU2hvK2gKbWxPcmRCcUtZWVJOUXRVR1AxS21lUWNGTVN2a01jbTZ5RERYQklnd3BEbnZQeCtyeGs4RlVtL1BudG9YSUZpegpOL0lEdVJTYjkzcll5Vkk5dkNsenlWRklhUWYySnRadUMwUTA2VGU4UUhSYjEvbzUycmtGcUJza3NIUFFidmxVCk5HbHgrcWpkWXBtYXZHT3NnajFaZzYzZHVWcVpnT2cyU0dvb0MzT1pJVFpBcmJRTGh5Rlh0L29iK1JKQlRqcEEKaVphQzJuY2VGS3NaYm9KVDFKT3gvNm9ZNU9IejROaG1RQURmR1JKc3JOQUhxSTJCSjhsSlBYTDVKYkdWTThObAoxNnFrei9TTHZ4M2x0L2xvU0lReXlYN3gyRGlQSEp0SG1DelFYSm8yclg0SkhUclpBUUtDQVFFQXpScVR4cW9JCmNHZUlhNFhBSWFJcDVMOXZQaGJBc3ExdnpQNjY3UlQrM0swSmdNNjB6ejFXQWNIaUVseSt2Z08wdGdvamw2VXIKY1ZrR25PUjdRazhrVU4zN1ZoQThGN3FxVkhYTmYzR0RnVFhTSnBlWVpXOWZ2VFRHTWJ6M2E4T05KK2J5M0hmUQpoWkhXK3hzcnJqVWY2UHkxSVgxL0dVbkxrclVrSjQzSS9aaXRndkFRWXdmRVBWMmgyaHZ5TU94aW9pbXZWWm5SClB6QXZlUHNRRWlCNFZCS0FwcElTejdVQ3lYMyt4UFludjM4RDhNZmpOWERsbXNEdHNpYmtlMXBpZ2Z4VGducWcKOWlmbTdqblMvNmc4Z3RtaDNTNncvanhSaHpISWo0NFEyallnbnRtV1JjSWV1SjMyZVo4Y0pHZWpEVFpyUnZiZAorc0RZTHllR2JuaE9FUUtDQVFFQXkrejQ4UG9OYUhPVk50NEl0UFBGTzFKMGhVaE9SNjBQZU5yR1ZjOXBwd0ZpCnJMQnBoSlRMN0lha0NnNGpMNXgxWGRjRlorN2oveUNqcXM2eUlkelRLS2Q4L2p2TnZtRExGRVRCZkFYWlQ2c0kKR0tscGlZS00yVVlNNFdUWTErTis4NWNTVjVjWnRHcDBYcnNEVVpNMEdTVmNQME1tdW94K2hjcW5SV21WUlNtNgpZUFlkQ0VqNU9vZ2ZMc091Wlc2Uk82d205b2I5aUZjU1VPWkVyZlV3aS9xOFVYeGIrSEtaV21lUHh2QlZ2VWJFCmpKS2U0aTN0aXEya21JMmtsS1A3c283a3pjN3hqeGtNV2R6eENGUHBpVXdYckYyb3F4ZWpjdEhsZnp5ckgzdHEKWmpaSkVsVWliUFphMDJ3TVdZVVBESElKVFNPUUx6a2daSWlKMElFRjhRS0NBUUJ5SlQxaXpkUStnbEFZenVuNwpqZDFVMGZsZUM1bVlsdzltZmNtWUVodzR3aEJNNEQwZUxOdUZ4TnBGTDlwenMycEorV0NQajd1YXJGb1N6TkdRCk9LMVVXQVg5Z3JGKzMwTFdHRzJTWktFME1yalBBNFVyeG96NHByczZpUmtGbjJJYWQ5eG5PRU1UWXZQSzhmY2cKVDY1L29zeEhaQ2xzOEhYY0l2V0pFYzV1M0I2TWhZQUpMUlJZdHBoUjQwV3BWcFVaL0tyNHI4OFZKSUwzQ0hxTQpMQWZyZWhTaEh5QmEramtmM0FBQ3E1KzVZajNXTGRoVU1JMkh6NkU1aVkyVTNOdC96ZWJIOGVsTXRTNC9IRzFDCkhPWDRTSmhkbVJPbG1mb2hqR0k3Q214MGxMVVkwcTFnQzBXL3B5RzUxQVA4ZXJUeXNIdUpsZkE2RWtMK0V6Z3MKbU1XUkFvSUJBR1ljVXNyWGFyOVNvUFRJV1RQQ25KQUh5YkN6Yy84UCtSOWE3TlVxOW94WmRUOWpWbks4engrdwppNU05WVFFR0p2N0dIRDdlcit1Z1BGUGJDL2RJeTdNZnFzYml5ekw0NUxkUmRsRFQzT3kzaDJaUjdqYWMyRW96CmVWK2pUN0ZLaU5jUVhtN2RpbEY4dktoeW5FYk1udG00aTJoaUI1S0JweGozVUtyQkk2MXVKNVZGcDJQdXBsMVkKcThTTzExU1MrTVlZZmFvVURmK25HZFRJZUx0aWV0eWN3STlNNWdDUjRaZ1RJenRLUEtSNk94RG9tZ1pIV1JxSAp1Rk5yR1c0VWJvUUthU2RrNkMvdkt2QVVZZHo1cDRiSVExSmIwNGxZWE0reHFtajd4L1FMcUV0bUExRGFzRkkvClNWdDBUdnJFVmJTMXBWS3E3QmU1VXk4RDczNG9IUUVDZ2dFQkFJeTdERGsvRjBVV1hVQlR0T1p2ZExIclpTZEYKQTJTdWxoU3RsbDFSbkw4TVYzd1hidHZtRXhINFBRYUZxNVdDUjBSTWFadGJsK2czM21MYlVCZXNPWm4ybmVPTwpoZ3VtNy9Qb1gwdU9OdC9FejhSWFdKbHRKQ1pqT3h0ZGFXMWMzMHRGNFE0bVg0bWRTU1Y1Q21wSkIwUFJIUzVaCm5IdFRWWlozUGpvQ09SWmpoZTJUUjQ1N291Z1hxUWtucEUvSWg2Uk5STVIwV2xYWkdmaStLQ2ptbkVWNjYwd2UKL2NCVU9iUDlHUkN3d0VoNkdVTDdkRVdpRkR1dDJuRnJOV05lcVpJTDJQQWkzaEtKZWxRZGZYZE9nVlFhS1Fycwpnb3dRaG43Um9xY3BNNmt6bTJRMlVoYzJnMG9FbUJjZ3ZMUFo0WklvQ1FGM25vMnhiTlBCaExyVFF1cz0KLS0tLS1FTkQgUlNBIFBSSVZBVEUgS0VZLS0tLS0K%22%2C%22BUILD_USER_SSH_PUBLIC_KEY%22%3A%22c3NoLXJzYSBBQUFBQjNOemFDMXljMkVBQUFBREFRQUJBQUFDQVFDalllOGhsR01pT2pjVkdHMG83VVJ3ajR1UHRoSXZyTnpwQThBMllja3NDMGtEaXpVamh6Wlo0SW1GQ1hFSWJ1bTVGYytDZ0JvVmFlTkJvZmhSQnJOTE0wREZQS0t4Yjl4V2NQMmY2bnVocU5zWDVoVndSSVlKMFE2bUNkb21FYlhNTGNoVFpERExWUW85N1dJWW9jN0hsODdFUE10WXM0eWRlcXNRUThhc3pyaGZFUlFiTVk3cy9IRTFtcHExQUxBb3lJVzJlOFVIL3RJSTg2TFYrV2tSeVNacy9PZHk1THk4aUhRTldYNS9mSjZuQWtnTi8rZHlZQ1RxY3pDaDh6TlEvYkZFeGMvYUMrQzh1K2NPTFFraDJtdDUxc2d4dGsycUFzVndneEw4L3dQTHpEaTlKNGhwTVM3QkZTdzBhOVRsOXpHNnByUlNWMERuM3NsWFg2NkRyU3NTQ09LU2VlTHA4c2ZGUkEyRDBMbEpYQlU3dHhmRno3elBZc3ZHSHdqVVQ0YmFEb25Wem1QR2FDd1U3RzM3c3RUaVFMWXVvT09xcWxiWXFYZGlxbVFlaFFIQ0RmQVpuYzNQR0pyMENCWHc1Rk1aWFJUUlNkMThkMndrSFh0bWw5V0NKd3JJTStZcDJvRDgveERyb3JRaTNGT2tUVXFwb0FWNjhPRHVzUEhaYW1vQ3lEMWx1azVaWXlkcHhmN2lpc1JRUFdIQVNJN01ZRlVhbmlPTG8vNEpPVU1ZVVlHbVlNTXhkUGFycW1qNTVqYm5VTlhLMm5XREQzQ21pKzVSNGUzelZxa0FISDY2YlUyZkE2dklORW5SUGVWaDdiWGxGVzhXcStxSHhEV0V3US9zTC92aTY5YXhYWHRMZ3cyZnZBQkRUYS91VnM4N0tBN2NmMWpUQVE9PSBlbGxpb3Qud3JpZ2h0QGludmlxYS5jb20K%22%7D&cgroupparent=&cpuperiod=0&cpuquota=0&cpusetcpus=&cpusetmems=&cpushares=0&dockerfile=.%2FDockerfile&labels=null&memory=0&memswap=0&rm=0&shmsize=0&t=quay.io%2Finviqa_images%2Fpolo%3A11e0586bbbd38bde0a3696c64aec9483e1a0c390&ulimits=null: io: read/write on closed pipe",
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
