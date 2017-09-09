package builder

import "testing"

func TestItConvertsTheURLSuccessfully(t *testing.T) {
    expectedResults := map[string]string{
        "https://continuous-pipe.firebaseio.com/raws/1234": "/raws/1234",
        "https://continuous-pipe.firebaseio.com/raws/1234.json": "/raws/1234",
        "/raws/1234.json": "/raws/1234",
        "https://continuous-pipe.firebaseio.com/raws/1234/.json": "/raws/1234",
        "https://continuous-pipe.firebaseio.com/raws/1234/.json?foo=bar": "/raws/1234",
    }

    for input, expected := range expectedResults {
        if expected != FirebasePathFromUrl(input) {
            t.Errorf("Expected '%s' but got '%s' for '%s'", expected, FirebasePathFromUrl(input), input)
        }
    }
}
