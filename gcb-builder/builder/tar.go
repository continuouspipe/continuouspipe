package builder

import (
    "path/filepath"
    "fmt"
    "archive/tar"
    "io"
    "os"
    "runtime"
    "strings"
)

func writeNewFile(fpath string, in io.Reader, fm os.FileMode) error {
    err := os.MkdirAll(filepath.Dir(fpath), 0755)
    if err != nil {
        return fmt.Errorf("%s: making directory for file: %v", fpath, err)
    }

    out, err := os.Create(fpath)
    if err != nil {
        return fmt.Errorf("%s: creating new file: %v", fpath, err)
    }
    defer out.Close()

    err = out.Chmod(fm)
    if err != nil && runtime.GOOS != "windows" {
        return fmt.Errorf("%s: changing file mode: %v", fpath, err)
    }

    _, err = io.Copy(out, in)
    if err != nil {
        return fmt.Errorf("%s: writing file: %v", fpath, err)
    }
    return nil
}

func writeNewSymbolicLink(fpath string, target string) error {
    err := os.MkdirAll(filepath.Dir(fpath), 0755)
    if err != nil {
        return fmt.Errorf("%s: making directory for file: %v", fpath, err)
    }

    err = os.Symlink(target, fpath)
    if err != nil {
        return fmt.Errorf("%s: making symbolic link for: %v", fpath, err)
    }

    return nil
}

func mkdir(dirPath string) error {
    err := os.MkdirAll(dirPath, 0755)
    if err != nil {
        return fmt.Errorf("%s: making directory: %v", dirPath, err)
    }
    return nil
}


// untar un-tarballs the contents of tr into destination.
func untar(tr *tar.Reader, destination string, stripInnerFolder bool) error {
    for {
        header, err := tr.Next()
        if err == io.EOF {
            break
        } else if err != nil {
            return err
        }

        if err := untarFile(tr, header, destination, stripInnerFolder); err != nil {
            return err
        }
    }
    return nil
}

// untarFile untars a single file from tr with header header into destination.
func untarFile(tr *tar.Reader, header *tar.Header, destination string, stripInnerFolder bool) error {
    fileName := header.Name
    if stripInnerFolder {
        slashIndex := strings.Index(fileName, "/")
        if slashIndex != -1 {
            fileName = fileName[slashIndex+1:]
        }
    }

    switch header.Typeflag {
    case tar.TypeDir:
        return mkdir(filepath.Join(destination, fileName))
    case tar.TypeReg, tar.TypeRegA:
        return writeNewFile(filepath.Join(destination, fileName), tr, header.FileInfo().Mode())
    case tar.TypeSymlink:
        return writeNewSymbolicLink(filepath.Join(destination, fileName), header.Linkname)
    case tar.TypeXGlobalHeader:
        return nil
    default:
        return fmt.Errorf("%s: unknown type flag: %c", fileName, header.Typeflag)
    }
}
