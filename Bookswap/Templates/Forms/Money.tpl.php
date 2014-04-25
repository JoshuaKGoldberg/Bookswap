<div class="money">
  <span class="dollas"><?php echo getCurrency(); ?></span>
  <input class="num_dollars" type='number' value='1' />
  <select class="num_cents cents">
    <?php
      echo '<option>00</option>';
      for($i = 25; $i <= 75; $i += 25)
        echo '<option>' . $i . '</option>';
    ?>
  </select>
</div>