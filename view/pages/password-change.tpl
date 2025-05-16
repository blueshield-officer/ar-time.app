{extends file='main-simple.tpl'}

{block name=page}{$pageTitle}{/block}

{block name=content}
    <script>
        let wasWrongPassword = {if $passwordError !== null}'static'{else} false {/if};
        let wasWrongPasswordRepeat = false;
    </script>
    <div class="container auth-main">
        <div id="auth-card">
            {if $changed === false}
            <h1 class="auth-title">Change Password</h1>

            <form class="auth-form" id="password-change-form" method="post" action="/password-change">
                <p class="auth-description">Set new password for <span id="reset-password-email-target" class="highlighted-text">{$email}</span></p>

                <div id="general-error" class="invalid-feedback d-block mb-3">{$generalError}</div>

                <div class="input-group mb-3 has-validation ">
                    <div id="password_box" class="form-floating {if $passwordError !== null}is-invalid{/if}">
                        <input oninput="validatePassword()" type="password" class="form-control  {if $passwordError !== null || $generalError !== null}is-invalid{/if}" id="password_input" name="password" placeholder="Password" autocomplete="on">
                        <label for="password_input">Password</label>
                    </div>
                    <div id="invalid-password-comment" class="invalid-feedback">{if $passwordError !== null}{$passwordError}{/if}</div>
                </div>
                <div class="input-group mb-3 has-validation">
                    <div id="passwordRepeat_box" class="form-floating {if $password2Error !== null}is-invalid{/if}">
                        <input oninput="validatePasswordRepeat()" type="password" class="form-control {if $password2Error !== null || $generalError !== null}is-invalid{/if}" id="passwordRepeat_input" name="password2" placeholder="Password" autocomplete="on">
                        <label for="passwordRepeat_input">Password Repeat</label
                    </div>
                    <div id="invalid-passwordRepeat-comment" class="invalid-feedback">{if $password2Error !== null}{$password2Error}{/if}</div>
                </div>
            </div>

            <!-- Hidden -->
            <input id="access_token" type="hidden" name="access_token" value="{$smarty.get.token}">

            <input id="method_input" type="hidden" name="method" value="password-change">
            <input id="csrf_token" type="hidden" name="csrf_token" value="{$smarty.session.csrf_token}">

            <button class="submit-button" type="button" onclick="checkAuthFields()">Change</button>
            </form>
            {else}
            <h1 class="auth-title">Password Changed</h1>
            <p>Your password was changed, now you can use it to login into your account.</p>
            <script>
                setTimeout(() => {
                    window.location.href = "/login";
                }, 3000);
            </script>
        {/if}
        </div>
    </div>
{/block}