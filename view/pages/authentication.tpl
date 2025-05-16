{extends file='main-simple.tpl'}

{block name=page}{capitalize str=$method}{/block}

{block name=content}
    <script>
        let wasWrongEmail = {if $emailError !== null}'static'{else} false {/if};
        let wasWrongPassword = {if $passwordError !== null}'static'{else} false {/if};
        let wasWrongPasswordRepeat = false;
    </script>
    <div class="container auth-main">
        <div id="auth-card">
            <h1 class="auth-title">{capitalize str=$method}</h1>

            {if $googleError !== null}
                <div class="mb-3">
                    <div id="general-error" class="invalid-feedback d-block">{$googleError}</div>
                </div>
            {/if}

            <a href="{$googleAuthRedirectLink}" class="auth-other">
                <img class="out-other-icon" src="/frontend/assets/img/icons/Google__G__logo.png" alt="google_icon">
                <p class="auth-other-text">Continue with Google</p>
            </a>

            <!-- OR -->
            <div class="or-block">
                <div class="or-separator"></div>
                <span class="or-text">or</span>
                <div class="or-separator"></div>
            </div>
            <!--  OR -->

            {if $generalError !== null}
                <div class="mb-3">
                    <div id="general-error" class="invalid-feedback d-block">{$generalError}</div>
                </div>
            {/if}

            <form class="auth-form" id="auth-form" method="post" action="/{format str=$method}">
                <!-- Info -->
                <div class="input-group mb-3 has-validation">
                    <div id="email_box" class="form-floating {if $emailError !== null && $emailError !== ''}is-invalid{/if}">
                        <input oninput="validateEmail()" type="email" class="form-control {if $emailError !== null || $generalError !== null}is-invalid{/if}" id="email_input" name="email" placeholder="name@example.com">
                        <label for="email_input">Email address</label>
                    </div>
                    <div id="invalid-email-comment" class="invalid-feedback">{if $emailError !== null}{$emailError}{/if}</div>
                </div>
                <div class="input-group mb-3 has-validation ">
                    <div id="password_box" class="form-floating {if $passwordError !== null}is-invalid{/if}">
                        <input oninput="validatePassword()" type="password" class="form-control  {if $passwordError !== null || $generalError !== null}is-invalid{/if}" id="password_input" name="password" placeholder="Password" autocomplete="on">
                        <label for="password_input">Password</label>
                    </div>
                    <div id="invalid-password-comment" class="invalid-feedback">{if $passwordError !== null}{$passwordError}{/if}</div>
                </div>
                {if $method === 'sign up'}
                <div class="input-group mb-3 has-validation">
                    <div id="passwordRepeat_box" class="form-floating {if $password2Error !== null}is-invalid{/if}">
                        <input oninput="validatePasswordRepeat()" type="password" class="form-control {if $password2Error !== null || $generalError !== null}is-invalid{/if}" id="passwordRepeat_input" name="password2" placeholder="Password" autocomplete="on">
                        <label for="passwordRepeat_input">Password Repeat</label
                    </div>
                    <div id="invalid-passwordRepeat-comment" class="invalid-feedback">{if $password2Error !== null}{$password2Error}{/if}</div>
                </div>
        </div>
        {/if}
        {if $method === 'login'}
            <p onclick="sendChangePasswordRequest()" class="forgot-password-button blue-text-link">Forgot password?</p>
            <p id="forgot-password-response"></p>
        {/if}
        <!-- Hidden -->
        <input id="csrf_token_input" type="hidden" name="csrf_token" value="{$smarty.session.csrf_token}">
        <input id="method_input" type="hidden" name="method" value="{format str=$method}">
        <button class="submit-button" type="button" onclick="checkAuthFields()">{capitalize str=$method}</button>
        <div class="has-validation">
            <div id="invalid-password-comment" class="invalid-feedback">Password must be at least 8 characters</div>
        </div>
        </form>
    </div>
    <p class="text-join-now">
        {if $method === 'login'}
            Not registered yet? <a class="blue-text-link" href="/signup">Join now</a>
        {else}
            Already got an account? <a class="blue-text-link" href="/login">Login here</a>
        {/if}
    </p>
    </div>
{/block}