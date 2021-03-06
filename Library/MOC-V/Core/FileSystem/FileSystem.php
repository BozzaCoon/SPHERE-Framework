<?php
namespace MOC\V\Core\FileSystem;

use MOC\V\Core\FileSystem\Component\Bridge\Repository\SymfonyFinder;
use MOC\V\Core\FileSystem\Component\Bridge\Repository\UniversalDownload;
use MOC\V\Core\FileSystem\Component\Bridge\Repository\UniversalStream;
use MOC\V\Core\FileSystem\Component\Bridge\Repository\UniversalFileLoader;
use MOC\V\Core\FileSystem\Component\Bridge\Repository\UniversalFileWriter;
use MOC\V\Core\FileSystem\Component\IBridgeInterface;
use MOC\V\Core\FileSystem\Component\IVendorInterface;
use MOC\V\Core\FileSystem\Component\Parameter\Repository\FileParameter;
use MOC\V\Core\FileSystem\Exception\FileSystemException;
use MOC\V\Core\FileSystem\Vendor\Vendor;

/**
 * Class FileSystem
 *
 * @package MOC\V\Core\FileSystem
 */
class FileSystem implements IVendorInterface
{

    /** @var IVendorInterface $VendorInterface */
    private $VendorInterface = null;

    /**
     * @param IVendorInterface $VendorInterface
     */
    public function __construct(IVendorInterface $VendorInterface)
    {

        $this->setVendorInterface($VendorInterface);
    }

    /**
     * @param string $Location
     *
     * @return IBridgeInterface
     * @throws FileSystemException
     */
    public static function getFileLoader($Location)
    {

        $Result = self::getUniversalFileLoader($Location);
        if (!$Result->getRealPath()) {
            return self::getSymfonyFinder($Location);
        }
        return $Result;
    }

    /**
     * @param string $Location
     *
     * @return IBridgeInterface
     */
    private static function getUniversalFileLoader($Location)
    {

        $Loader = new FileSystem(
            new Vendor(
                new UniversalFileLoader(
                    new FileParameter($Location)
                )
            )
        );

        return $Loader->getBridgeInterface();
    }

    /**
     * @return IBridgeInterface
     */
    public function getBridgeInterface()
    {

        return $this->VendorInterface->getBridgeInterface();
    }

    /**
     * @param string $Location
     *
     * @return IBridgeInterface
     */
    private static function getSymfonyFinder($Location)
    {

        $Loader = new FileSystem(
            new Vendor(
                new SymfonyFinder(
                    new FileParameter($Location)
                )
            )
        );

        return $Loader->getBridgeInterface();
    }

    /**
     * @param string      $Location
     * @param null|string $Filename
     *
     * @return IBridgeInterface
     * @throws FileSystemException
     */
    public static function getDownload($Location, $Filename = null)
    {

        return self::getUniversalDownload($Location, $Filename);
    }

    /**
     * @param string      $Location
     * @param null|string $Filename
     *
     * @return IBridgeInterface
     * @throws FileSystemException
     */
    public static function getStream($Location, $Filename = null)
    {

        return self::getUniversalStream($Location, $Filename);
    }

    /**
     * @param string      $Location
     * @param null|string $Filename
     *
     * @return IBridgeInterface
     */
    private static function getUniversalDownload($Location, $Filename = null)
    {

        $Loader = new FileSystem(
            new Vendor(
                new UniversalDownload(
                    new FileParameter($Location),
                    new FileParameter($Filename)
                )
            )
        );

        return $Loader->getBridgeInterface();
    }

    /**
     * @param string      $Location
     * @param null|string $Filename
     *
     * @return IBridgeInterface
     */
    private static function getUniversalStream($Location, $Filename = null)
    {

        $Loader = new FileSystem(
            new Vendor(
                new UniversalStream(
                    new FileParameter($Location),
                    new FileParameter($Filename)
                )
            )
        );

        return $Loader->getBridgeInterface();
    }

    /**
     * @param string $Location
     *
     * @return IBridgeInterface
     * @throws FileSystemException
     */
    public static function getFileWriter($Location)
    {

        return self::getUniversalFileWriter($Location);
    }

    /**
     * @param string $Location
     *
     * @return IBridgeInterface
     */
    private static function getUniversalFileWriter($Location)
    {

        $Loader = new FileSystem(
            new Vendor(
                new UniversalFileWriter(
                    new FileParameter($Location)
                )
            )
        );

        return $Loader->getBridgeInterface();
    }

    /**
     * @return IVendorInterface
     */
    public function getVendorInterface()
    {

        return $this->VendorInterface;
    }

    /**
     * @param IVendorInterface $VendorInterface
     *
     * @return IVendorInterface
     */
    public function setVendorInterface(IVendorInterface $VendorInterface)
    {

        $this->VendorInterface = $VendorInterface;
        return $this;
    }

    /**
     * @param IBridgeInterface $BridgeInterface
     *
     * @return IBridgeInterface
     */
    public function setBridgeInterface(IBridgeInterface $BridgeInterface)
    {

        return $this->VendorInterface->setBridgeInterface($BridgeInterface);
    }
}
