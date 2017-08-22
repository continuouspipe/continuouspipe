package puller

import (
    "cloud.google.com/go/pubsub"
    "encoding/base64"
    "fmt"
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
    body := base64.StdEncoding.EncodeToString(message.Data)
    result := emh.executer.Execute(emh.factory.Create(body), true)

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
    }

    return err
}
