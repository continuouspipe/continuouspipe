<?php

namespace ContinuousPipe\Pipe\Kubernetes\Transformer;

use ContinuousPipe\Model\Component;
use Kubernetes\Client\Model\Volume;

class VolumeTransformer
{
    private static $classMapping = [
        Component\Volume\EmptyDirectory::class => ['setter' => 'setEmptyDir', 'converter' => 'emptyDirConverter'],
        Component\Volume\NFS::class => ['setter' => 'setNfs', 'converter' => 'nfsConverter'],
        Component\Volume\HostPath::class => ['setter' => 'setHostPath', 'converter' => 'hostPathConverter'],
        Component\Volume\Persistent::class => ['setter' => 'setPersistentVolumeClaim', 'converter' => 'persistentVolumeClaimConverter'],
    ];

    /**
     * Create the Kubernetes volume object from the given component volume.
     *
     * @param Component\Volume $componentVolume
     *
     * @return Volume
     */
    public function getVolumeFromComponentVolume(Component\Volume $componentVolume)
    {
        $volume = new Volume($componentVolume->getName());
        $volumeClass = get_class($componentVolume);

        if (!array_key_exists($volumeClass, self::$classMapping)) {
            throw new \RuntimeException(sprintf(
                'Volume of type "%s" is not supported yet',
                $volumeClass
            ));
        }

        $settings = self::$classMapping[$volumeClass];
        $converter = $settings['converter'];
        $setter = $settings['setter'];

        $source = $this->$converter($componentVolume);
        $volume->$setter($source);

        return $volume;
    }

    private function emptyDirConverter(Component\Volume\EmptyDirectory $emptyDirectory)
    {
        return new Volume\EmptyDirVolumeSource();
    }

    private function nfsConverter(Component\Volume\NFS $nfs)
    {
        return new Volume\NfsVolumeSource($nfs->getServer(), $nfs->getPath(), $nfs->isReadOnly());
    }

    private function hostPathConverter(Component\Volume\HostPath $hostPath)
    {
        return new Volume\HostPathVolumeSource($hostPath->getPath());
    }

    private function persistentVolumeClaimConverter(Component\Volume\Persistent $persistent)
    {
        return new Volume\PersistentVolumeClaimSource($persistent->getName());
    }
}
