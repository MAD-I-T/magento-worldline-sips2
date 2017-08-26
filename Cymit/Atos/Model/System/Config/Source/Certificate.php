<?php
namespace Cymit\Atos\Model\System\Config\Source;
use \Magento\Framework\App\Filesystem\DirectoryList;

class Certificate  implements \Magento\Framework\Option\ArrayInterface
{

    protected $_options;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

     public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
         $this->directoryList = $directoryList;
    }
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = array();
            $this->_options[] = array('value' => '', 'label' => Mage::helper('adminhtml')->__('-- Please select --'));
            $relativePath = 'lib' . DS . 'atos' . DS . 'param';
            $absolutePath =  $this->directoryList ->getPath(DirectoryList::ROOT) . DS . $relativePath;

            if (is_dir($absolutePath)) {
                $dir = dir($absolutePath);
                while ($file = $dir->read()) {
                    if (preg_match("/^certif/i", $file)) {
                        $this->_options[] = array('value' => $relativePath . DS . $file, 'label' => $file);
                    }
                }

                $dir->close();
            }
            sort($this->_options);
        }
        return $this->_options;
    }

}
