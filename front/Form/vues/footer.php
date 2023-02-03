</div>
<script src="javascript/boxicons.js"></script>
<script>window.jQuery || document.write('<script src="js/vendor/jquery-3.3.1.min.js"><\/script>')</script>
<script src="trumbowyg/dist/trumbowyg.min.js"></script>
<script src="javascript/script.js"></script>
<script>
    $('#editor').trumbowyg({
        btns: [
            ['strong', 'em'],
            ['justifyLeft', 'justifyCenter'],
            ['insertImage', 'link']
        ]});
    </script>
<?php Html::footer(); ?>