<?php if (count($errors) > 0): ?>
  <div class="error" style="color: red; margin-bottom: 15px;">
    <?php foreach ($errors as $error): ?>
      <p><?php echo $error; ?></p>
    <?php endforeach ?>
  </div>
<?php endif ?>
