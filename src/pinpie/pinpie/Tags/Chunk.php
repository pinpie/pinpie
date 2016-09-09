<?php

namespace pinpie\pinpie\Tags;


class Chunk extends Snippet {
  
  protected function getContent() {
    return file_get_contents($this->filename);
  }
}