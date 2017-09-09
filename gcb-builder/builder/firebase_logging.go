package builder

import (
	"encoding/json"
	"fmt"
	"io"
	"time"

	"github.com/zabawaba99/firego"
	"golang.org/x/oauth2"
	"golang.org/x/oauth2/google"
	"net/url"
)

type FirebaseLoggedStepRunner struct {
	stepRunner     StepRunner
	firebaseClient *firego.Firebase
	parentPath     string
}

func NewFirebaseLoggedStepRunner(stepRunner StepRunner, loggingConfiguration FirebaseLoggingConfiguration) (*FirebaseLoggedStepRunner, error) {
	serviceAccount, err := json.Marshal(loggingConfiguration.ServiceAccount)
	if err != nil {
		return nil, err
	}

	conf, err := google.JWTConfigFromJSON(
		serviceAccount,
		"https://www.googleapis.com/auth/userinfo.email",
		"https://www.googleapis.com/auth/firebase.database",
	)

	if err != nil {
		return nil, err
	}

	return &FirebaseLoggedStepRunner{
		stepRunner:     stepRunner,
		firebaseClient: firego.New(loggingConfiguration.DatabaseUrl, conf.Client(oauth2.NoContext)),
		parentPath:     loggingConfiguration.ParentLog,
	}, nil
}

func (r FirebaseLoggedStepRunner) ReadArtifact(step ManifestStep, artifact Artifact) error {
	log := r.wrap(fmt.Sprintf("Reading artifact <code>%s</code>", artifact.Name))
	err := r.stepRunner.ReadArtifact(step, artifact)
	r.unwrap(log, err)
	return err
}

func (r FirebaseLoggedStepRunner) WriteArtifact(step ManifestStep, builtImage string, artifact Artifact) error {
	log := r.wrap(fmt.Sprintf("Writing artifact <code>%s</code>", artifact.Name))
	err := r.stepRunner.WriteArtifact(step, builtImage, artifact)
	r.unwrap(log, err)
	return err
}

func (r FirebaseLoggedStepRunner) BuildImage(manifest Manifest, step ManifestStep, output io.Writer) (string, error) {
	output, log := r.wrapOutputIn(output, fmt.Sprintf("Building Docker image %s", ImageNameForDisplay(step)))

	builtImage, err := r.stepRunner.BuildImage(manifest, step, output)

	r.unwrap(log, err)

	return builtImage, err
}

func (r FirebaseLoggedStepRunner) PushImage(manifest Manifest, step ManifestStep, output io.Writer) error {
	output, log := r.wrapOutputIn(output, fmt.Sprintf("Pushing Docker image %s", ImageNameForDisplay(step)))

	err := r.stepRunner.PushImage(manifest, step, output)

	r.unwrap(log, err)

	return err
}

func (r FirebaseLoggedStepRunner) CleanUpWroteArtifacts(step ManifestStep) error {
	return r.stepRunner.CleanUpWroteArtifacts(step)
}

func (r FirebaseLoggedStepRunner) CleanUpReadArtifacts(step ManifestStep) error {
	return r.stepRunner.CleanUpReadArtifacts(step)
}

func (r FirebaseLoggedStepRunner) Check() error {
	return r.stepRunner.Check()
}

func (r FirebaseLoggedStepRunner) unwrap(child *firego.Firebase, err error) {
	if child == nil {
		return
	}

	var status string
	if err != nil {
		status = "failure"

		child.Child("children").Push(map[string]string{
			"type":     "text",
			"contents": fmt.Sprint(err),
		})
	} else {
		status = "success"
	}

	child.Update(map[string]string{
		"status":      status,
		status + "At": time.Now().UTC().Format(time.RFC3339),
	})
}

func (r FirebaseLoggedStepRunner) wrap(title string) *firego.Firebase {
	v := map[string]string{
		"type":      "text",
		"contents":  fmt.Sprintf(title),
		"status":    "running",
		"runningAt": time.Now().UTC().Format(time.RFC3339),
	}

	child, err := r.firebaseClient.Child(r.parentPath + "/children").Push(v)
	if err != nil {
		fmt.Println(err)

		child = nil
	}

	return child
}

func (r FirebaseLoggedStepRunner) wrapOutputIn(output io.Writer, title string) (io.Writer, *firego.Firebase) {
	child := r.wrap(title)

	if child != nil {
		raws, err := r.firebaseClient.Ref("raws")
		if err != nil {
			fmt.Println(err)

			return output, child
		}

		rawChild, err := raws.Push(map[string]string{
			"type": "raw",
			"logId": child.String(),
		})

		if err != nil {
			fmt.Println(err)

			return output, child
		}

		_, err = child.Child("children").Push(map[string]string{
			"type": "raw",
			"path": FirebasePathFromUrl(rawChild.String()),
		})

		if err != nil {
			fmt.Println(err)
		}

		output = io.MultiWriter(output, NewFirebaseRawChildrenWriter(rawChild.Child("children")))
	}

	return output, child
}

type FirebaseRawChildrenWriter struct {
	firebaseClient *firego.Firebase
}

func NewFirebaseRawChildrenWriter(firebaseClient *firego.Firebase) FirebaseRawChildrenWriter {
	return FirebaseRawChildrenWriter{
		firebaseClient: firebaseClient,
	}
}

func (w FirebaseRawChildrenWriter) Write(p []byte) (n int, err error) {
	_, err = w.firebaseClient.Push(map[string]string{
		"type":     "text",
		"contents": string(p),
	})

	return len(p), err
}

func FirebasePathFromUrl(urlString string) string {
	u, err := url.Parse(urlString)
	if err != nil {
		return urlString
	}

	if u.Path[len(u.Path)-5:] == ".json" {
		u.Path = u.Path[0:len(u.Path)-5]
	}

	if u.Path[len(u.Path)-1:] == "/" {
		u.Path = u.Path[0:len(u.Path)-1]
	}

	return u.Path
}
