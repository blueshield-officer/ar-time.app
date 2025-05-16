{extends file='main-simple.tpl'}

{block name=page}{$pageTitle}{/block}

{block name=content}
    <div class="container auth-main">
        <div id="auth-card">
            {if $registered === false}
                <h1 class="auth-title">Code Verification</h1>

                <form class="auth-form" id="auth-form-code" method="post" action="/signup">
                    <p>Code was sent to <span class="highlighted-text">{$email}</span></p>

                    <div class="code-input">
                        <input type="text" name="int1" class="verificationCodeElement" maxlength="1" required>
                        <input type="text" name="int2" class="verificationCodeElement" maxlength="1" required>
                        <input type="text" name="int3" class="verificationCodeElement" maxlength="1" required>
                        <input type="text" name="int4" class="verificationCodeElement" maxlength="1" required>
                        <input type="text" name="int5" class="verificationCodeElement" maxlength="1" required>
                        <input type="text" name="int6" class="verificationCodeElement" maxlength="1" required>
                    </div>
                    <div id="general-error" class="invalid-feedback d-block mb-3">{$generalError}</div>
                    <div class="code-info">
                        <p>Left time: <span id="code-verify-left-time" class="warning highlighted-text"></span></p>
                        <p>Tries left: <span id="code-verify-left-tries" class="danger highlighted-text">{$leftTries}</span></p>
                    </div>

                    <script>
                        document.addEventListener("DOMContentLoaded", (event) => {
                            const interval = setInterval(changeLeftTime, 1000);

                            let leftSeconds = {$expires}

                            function changeLeftTime() {
                                if (leftSeconds < 0){
                                    window.location.href = "/signup";
                                    clearInterval(interval)
                                    return;
                                }

                                document.getElementById("code-verify-left-time").innerText = secondsToTimeStr(leftSeconds);
                                leftSeconds -= 1
                            }
                            changeLeftTime()
                        })
                    </script>

                    <!-- Hidden -->
                    <input id="csrf_token_input" type="hidden" name="csrf_token" value="{$smarty.session.csrf_token}">
                    <button class="submit-button btn-turned-off" type="submit" id="verify-code-btn" disabled>Verify</button>
                </form>
                {else}
                <h1 class="auth-title">Registered</h1>
                <p>Your account has been created, now you can log in into it.</p>
                <script>
                    setTimeout(() => {
                        window.location.href = "/login";
                    }, 5000);
                </script>
            {/if}
        </div>
    </div>
{/block}