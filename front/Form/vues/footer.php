</div>
<script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="js/vendor/jquery-3.3.1.min.js"><\/script>')</script>
<script src="trumbowyg/dist/trumbowyg.min.js"></script>
<script src="script.js"></script>
<script>
    $('#editor').trumbowyg({
        btns: [
            ['strong', 'em'],
            ['justifyLeft', 'justifyCenter'],
            ['insertImage', 'link']
        ]});
    </script>
<?php Html::footer(); ?>