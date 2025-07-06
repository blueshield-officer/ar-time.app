{if $loggedIn === true}
    <div class="mb-4 section-card border-color-theme margin-16">
        <div class="part border-color-theme account-info">
            <div class="account-title">
                <div>
                    <h3>{$userName}</h3>
                    <p>{$userEmail}</p>
                </div>
                <p class="account-badge account-badge-{if $additionalAccountInfo['type'] === 'student'}mortal{elseif $additionalAccountInfo['type'] === 'staff'}immortal{/if}">{capitalize str=$additionalAccountInfo['type']}</p>
            </div>
            {if $additionalAccountInfo['graduation_year'] !== null && $additionalAccountInfo['type'] === 'student'}
                <div>
                    <span class="{$additionalAccountInfo['grade']} golden-5 grade">{capitalize str={$additionalAccountInfo['grade']}}</span> <span>Class of </span> <span class="highlighted-text">{$additionalAccountInfo['graduation_year']}</span>
                </div>
            {/if}
            <div class="d-flex justify-content-between">
                <div></div>
                {*                    <button data-usersure="0" class="action-button change-password-button" id="change-password-button">*}
                {*                        Change Password*}
                {*                    </button>*}
                <button data-usersure="0" class="action-button log-out-button" id="logout-button">
                    Log Out
                </button>
            </div>
        </div>
    </div>
{/if}