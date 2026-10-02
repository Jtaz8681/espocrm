/************************************************************************
 * BugZyro Enterprise System
 *
 * Copyright (C) 2026 Ethos Dive Software. All Rights Reserved.
 *
 * Developed and engineered by Ethos Dive Software.
 * Intellectual property of Ethos Dive Software, with rights of use
 * granted exclusively to BugZyro.
 *
 * PROPRIETARY AND CONFIDENTIAL:
 * This file and the underlying source code are proprietary assets of
 * Ethos Dive Software. Unauthorized copying, distribution, modification,
 * reverse engineering, or public display of this software, via any medium,
 * is strictly prohibited without prior written authorization from
 * Ethos Dive Software.
 ************************************************************************/

import LoginHandler from 'handlers/login';
import Base64 from 'js-base64';

class OidcLoginHandler extends LoginHandler {

    /** @inheritDoc */
    process() {
        const proxy = window.open(
            'about:blank',
            'ConnectWithOAuth',
            'location=0,status=0,width=800,height=800'
        );

        Espo.Ui.notifyWait();

        return new Promise((resolve, reject) => {
            Espo.Ajax.getRequest('Oidc/authorizationData')
                .then(data => {
                    Espo.Ui.notify(false);

                    this.processWithData(data, proxy)
                        .then(info => {
                            const code = info.code;
                            const nonce = info.nonce;

                            const authString = Base64.encode('**oidc:' + code);

                            const headers = {
                                'Espo-Authorization': authString,
                                'Authorization': 'Basic ' + authString,
                                'X-Oidc-Authorization-Nonce': nonce,
                            };

                            resolve(headers);
                        })
                        .catch(() => {
                            proxy.close();
                            reject();
                        });
                })
                .catch(() => {
                    Espo.Ui.notify(false)

                    proxy.close();
                    reject();
                });
        });
    }

    /**
     * @private
     * @param {{
     *  endpoint: string,
     *  clientId: string,
     *  redirectUri: string,
     *  scopes: string[],
     *  claims: string|null,
     *  prompt: 'login'|'consent'|'select_account',
     *  maxAge: Number|null,
     *  codeChallenge: string|null,
     *  codeChallengeMethod: string|null
     * }} data
     * @param {WindowProxy} proxy
     * @return {Promise<{code: string, nonce: string}>}
     */
    processWithData(data, proxy) {
        const state = (Math.random() + 1).toString(36).substring(4);
        const nonce = (Math.random() + 1).toString(36).substring(4);

        const params = {
            client_id: data.clientId,
            redirect_uri: data.redirectUri,
            response_type: 'code',
            scope: data.scopes.join(' '),
            state: state,
            nonce: nonce,
            prompt: data.prompt,
        };

        if (data.codeChallenge && data.codeChallengeMethod) {
            params.code_challenge = data.codeChallenge;
            params.code_challenge_method = data.codeChallengeMethod;
        }

        if (data.maxAge || data.maxAge === 0) {
            params.max_age = data.maxAge;
        }

        if (data.claims) {
            params.claims = data.claims;
        }

        const partList = Object.entries(params)
            .map(([key, value]) => {
                return key + '=' + encodeURIComponent(value);
            });

        const url = data.endpoint + '?' + partList.join('&');

        return this.processWindow(url, state, nonce, proxy);
    }

    /**
     * @private
     * @param {string} url
     * @param {string} state
     * @param {string} nonce
     * @param {WindowProxy} proxy
     * @return {Promise<{code: string, nonce: string}>}
     */
    processWindow(url, state, nonce, proxy) {
        proxy.location.href = url;

        return new Promise((resolve, reject) => {
            const fail = () => {
                window.clearInterval(interval);

                if (!proxy.closed) {
                    proxy.close();
                }

                reject();
            };

            const interval = window.setInterval(() => {
                if (proxy.closed) {
                    fail();

                    return;
                }

                let url;

                try {
                    url = proxy.location.href;
                } catch (e) {
                    return;
                }

                if (!url) {
                    return;
                }

                const parsedData = this.parseWindowUrl(url);

                if (!parsedData) {
                    fail();
                    Espo.Ui.error('Could not parse URL', true);

                    return;
                }

                if ((parsedData.error || parsedData.code) && parsedData.state !== state) {
                    fail();
                    Espo.Ui.error('State mismatch', true);

                    return;
                }

                if (parsedData.error) {
                    fail();

                    const message = parsedData.errorDescription || this.loginView.translate('Error') + '\n' +
                        parsedData.error;

                    Espo.Ui.error(message, true);

                    console.log(parsedData);

                    return;
                }

                if (parsedData.code) {
                    window.clearInterval(interval);
                    proxy.close();

                    resolve({
                        code: parsedData.code,
                        nonce: nonce,
                    });
                }
            }, 300);
        });
    }

    /**
     * @param {string} url
     * @return {?{
     *     code: ?string,
     *     state: ?string,
     *     error: ?string,
     *     errorDescription: ?string,
     * }}
     */
    parseWindowUrl(url) {
        try {
            const params = new URL(url).searchParams;

            return {
                code: params.get('code'),
                state: params.get('state'),
                error: params.get('error'),
                errorDescription: params.get('errorDescription'),
            };
        }
        catch (e) {
            return null;
        }
    }
}

export default OidcLoginHandler;
