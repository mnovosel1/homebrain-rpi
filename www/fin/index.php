<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-combobox/1.1.8/css/bootstrap-combobox.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-combobox/1.1.8/js/bootstrap-combobox.min.js"></script>

  <script>
    $(document).ready(function(){
      $('.combobox').combobox();

      // bonus: add a placeholder
      $('.combobox').attr('placeholder', 'For example, start typing "Pennsylvania"');
    });
  </script>

<select class="combobox form-control">
  <option></option>
  <option value="PA">Pennsylvania</option>
  <option value="CT">Connecticut</option>
  <option value="NY">New York</option>
  <option value="MD">Maryland</option>
  <option value="VA">Virginia</option>
</select>
