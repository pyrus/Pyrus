  <h2>Tests covering line <?php echo $context->line; ?> of <?php echo $context->name(); ?></h2>
  <p>
   <a href="<?php echo $parent->context->getRootLink(); ?>">Aggregate Code Coverage for all tests</a>
  </p>
  <p>
   <a href="<?php echo $parent->context->getFileLink($context->name()); ?>">File <?php echo $context->name(); ?> code coverage</a>

  </p>
  <ul>
   <?php foreach ($context as $testfile): ?>
   <li>
    <a href="<?php echo $parent->context->getTOClink($testfile); ?>"><?php echo str_replace($context->testpath() . '/', '', $testfile); ?></a>
   </li>
   <?php endforeach; ?>
  </ul>