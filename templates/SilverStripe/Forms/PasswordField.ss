<input $AttributesHTML />
<% if $ShowViewPasswordToggle %>
    <style>
        @font-face {
            font-family: "silverstripe";
            src:url("_resources/vendor/silverstripe/admin/client/dist/fonts/silverstripe.eot");
            src:url("_resources/vendor/silverstripe/admin/client/dist/fonts/silverstripe.eot?#iefix") format("embedded-opentype"),
                url("_resources/vendor/silverstripe/admin/client/dist/fonts/silverstripe.woff") format("woff"),
                url("_resources/vendor/silverstripe/admin/client/dist/fonts/silverstripe.ttf") format("truetype"),
                url("_resources/vendor/silverstripe/admin/client/dist/fonts/silverstripe.svg#silverstripe") format("svg");
            font-weight: normal;
            font-style: normal;
        }

        [class^="font-icon-"]:before,
        [class*=" font-icon-"]:before {
        font-family: "silverstripe" !important;
        font-style: normal !important;
        font-weight: normal !important;
        font-variant: normal !important;
        text-transform: none !important;
        speak: none;
        line-height: 1;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        }

        .password .middleColumn {
            position: relative;
        }

        .password .middleColumn i {
            position: absolute;
            top: 50%;
            right: 5%;
            font-size: 1.5rem;
            transform: translate(-50%, -50%);
            cursor: pointer;
        }

        .font-icon-eye:before {
            content: "\\6c";
        }
        .font-icon-eye-with-line:before {
            content: "\\e01d";
        }
    </style>
    <i class="font-icon-eye-with-line" id="toggle_$ID"></i>
    <script>
        const togglePassword = document.querySelector('#toggle_$ID');
        const password = document.querySelector('#$ID');
        togglePassword.addEventListener('click', () => {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            togglePassword.classList.toggle('font-icon-eye');
            togglePassword.classList.toggle('font-icon-eye-with-line');
        });
    </script>
<% end_if %>
