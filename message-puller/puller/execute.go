package puller

import (
    "log"
    "os/exec"
    "syscall"
    "strings"
)

type CommandFactory struct {
    Cmd  string
    Args []string
}

func (me CommandFactory) Create(body string) *exec.Cmd {
    return exec.Command(me.Cmd, append(me.Args, body)...)
}

type CommandExecuter struct {
    errLogger *log.Logger
    infLogger *log.Logger
}

func NewCommandFactory(baseCmd string) *CommandFactory {
    var pcs []string
    if split := strings.Split(baseCmd, " "); len(split) > 1 {
        baseCmd, pcs = split[0], split[1:]
    }
    return &CommandFactory{
        Cmd:  baseCmd,
        Args: pcs,
    }
}

func NewCommandExecuter(errLogger, infLogger *log.Logger) *CommandExecuter {
    return &CommandExecuter{
        errLogger: errLogger,
        infLogger: infLogger,
    }
}

func (me CommandExecuter) Execute(cmd *exec.Cmd, output bool) int {
    me.infLogger.Println("Processing message...")

    var err interface{Error() string} = nil
    if output {
        cmd.Stdout = NewLogWriter(me.infLogger)
        cmd.Stderr = NewLogWriter(me.errLogger)
        err = cmd.Run()
    } else if out, outErr := cmd.CombinedOutput(); outErr != nil {
        me.errLogger.Printf("Failed: %s\n", string(out[:]))
        err = outErr
    }

    if err != nil {
        me.infLogger.Println("Failed. Check error log for details.")
        me.errLogger.Printf("Error: %s\n", err)

        if exiterr, ok := err.(*exec.ExitError); ok {
            if status, ok := exiterr.Sys().(syscall.WaitStatus); ok {
                return status.ExitStatus();
            }
        }

        return 1
    }

    me.infLogger.Println("Processed!")

    return 0
}

type LogWriter struct {
    logger *log.Logger
}

func NewLogWriter(l *log.Logger) *LogWriter {
    lw := &LogWriter{}
    lw.logger = l
    return lw
}

func (lw LogWriter) Write (p []byte) (n int, err error) {
    lw.logger.SetFlags(0)
    lw.logger.Printf("%s", p)
    lw.logger.SetFlags(log.Ldate|log.Ltime)
    return len(p), nil
}
