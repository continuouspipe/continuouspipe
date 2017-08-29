package puller

import (
    "cloud.google.com/go/pubsub"
    "encoding/base64"
    "fmt"
    "k8s.io/kubernetes/pkg/util/json"
    "time"
)

func NewExecuteMessageHandler(executer *CommandExecuter, factory *CommandFactory) *ExecuteMessageHandler {
    return &ExecuteMessageHandler{
        executer: executer,
        factory: factory,
    }
}

type MessageHandler interface {
    Handle(message *pubsub.Message) error
}

type ExecuteMessageHandler struct {
    executer *CommandExecuter
    factory  *CommandFactory
}

func (emh *ExecuteMessageHandler) Handle(message *pubsub.Message) error {
    attributesAsJson, err := json.Marshal(message.Attributes)
    if err != nil {
        return err
    }

    body := base64.StdEncoding.EncodeToString(message.Data)
    attributes := base64.StdEncoding.EncodeToString(attributesAsJson)
    result := emh.executer.Execute(emh.factory.Create(body, attributes), true)

    if 0 != result {
        return fmt.Errorf("Command returned status code: %d", result)
    }

    return nil
}

func NewAcknowledgeIfNoErrorHandler(decoratedHandler MessageHandler) *AcknowledgeIfNoErrorHandler {
    return &AcknowledgeIfNoErrorHandler{
        decoratedHandler: decoratedHandler,
    }
}

type AcknowledgeIfNoErrorHandler struct{
    decoratedHandler MessageHandler
}

func (me *AcknowledgeIfNoErrorHandler) Handle(message *pubsub.Message) error {
    err := me.decoratedHandler.Handle(message)

    if err == nil {
        message.Ack()
    } else {
        message.Nack()

        // To prevent hamerring the infrastructure, with sleep
        time.Sleep(1 * time.Second)
    }

    return err
}
