package main

import (
    "github.com/continuouspipe/cloud-builder/builder"

    "flag"
    "fmt"
    "os"
)

func main() {
    manifestFilePath := flag.String("manifest", "continuouspipe.build-manifest.json", "the build manifest to be used to build")
    flag.Parse()

    b, err := builder.NewBuilder()
    if err != nil {
        fmt.Println(err)
        os.Exit(1)
    }

    args := flag.Args()
    if len(args) > 0 && args[0] == "check" {
        if err = b.Check(); err != nil {
            fmt.Println(err)
            os.Exit(1)
        }

        os.Exit(0)
    }

    manifest, err := builder.ReadManifest(*manifestFilePath)
    if err != nil {
        fmt.Println(err)
        os.Exit(1)
    }

    fmt.Println(manifest.LogBoundary+":BUILD")
    if err = b.Build(manifest); err != nil {
        fmt.Println(err)
        os.Exit(1)
    }

    fmt.Println(manifest.LogBoundary+":PUSH")
    if err = b.Push(manifest); err != nil {
        fmt.Println(err)
        os.Exit(1)
    }

    fmt.Println(manifest.LogBoundary+":END")
}
