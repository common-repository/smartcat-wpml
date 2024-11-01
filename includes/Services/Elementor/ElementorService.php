<?php

namespace Smartcat\Includes\Services\Elementor;

use Smartcat\Includes\Services\Elementor\Resources\ElementorPostStatus;

class ElementorService
{
    /** @var array */
    private $data = [];

    /** @var array */
    private $elements = [];

    public function checkPost(int $postID): ElementorPostStatus
    {
        $postStatus = new ElementorPostStatus();

        if (is_elementor_installed()) {
            try {
                $elementorDocument = new Document($postID);
                $postStatus->setIsBuiltWithElementor(
                    $elementorDocument->isBuiltWithElementor()
                );
                $this->data = $elementorDocument->getData();
                $this->elements = $elementorDocument->getElements(true);
            } catch (\Throwable $exception) {
                $postStatus->setHasErrors(true);
                sc_log()->warn('Error Elementor post. Content will be exported of Gutenberg editor.', [
                    'postId' => $postID,
                    'error' => $exception->getMessage(),
                    'stackTrace' => $exception->getTraceAsString()
                ]);
            }
        }

        return $postStatus;
    }

    /**
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function elements(): array
    {
        return $this->elements;
    }
}