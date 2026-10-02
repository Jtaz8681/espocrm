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

describe('theme-manager', () => {
    /** @type {module:models/settings} */
	let config;
    /** @type {module:models/preferences} */
    let preferences;
    /** @type {module:metadata} */
    let metadata;
    /** @type {module:theme-manager} */
    let themeManager;

	beforeEach(done => {
        require(['models/settings', 'models/preferences', 'metadata', 'theme-manager'],
        (Config, Preferences, Metadata, ThemeManager) => {
            config = new Config();
            preferences = new Preferences();
            metadata = new Metadata();
            themeManager = new ThemeManager(config, preferences, metadata)

            metadata.data = {
                themes: {
                    "Espo": {
                        "params": {
                            "navbar": {
                                "type": "enum",
                                "default": "side",
                                "options": [
                                    "side",
                                    "top"
                                ]
                            }
                        },
                        "mappedParams": {
                            "navbarHeight": {
                                "param": "navbar",
                                "valueMap": {
                                    "side": 30,
                                    "top": 43
                                }
                            }
                        },
                        "someParam": "someValue"
                    }
                }
            };

            done();
        });
	});

	it('name', () => {
        spyOn(preferences, 'get').and.callFake(name => {
           if (name === 'theme') {
               return 'Test';
           }
        });

		expect(themeManager.getName()).toBe('Test');
	});

    it('param', () => {
        spyOn(preferences, 'get').and.callFake(name => {
            if (name === 'theme') {
                return 'Espo';
            }
        });

        expect(themeManager.getParam('someParam')).toBe('someValue');
    });

    it('parent param', () => {
        spyOn(preferences, 'get').and.callFake(name => {
            if (name === 'theme') {
                return 'Test';
            }
        });

        expect(themeManager.getParam('someParam')).toBe('someValue');
    });

    it('default param', () => {
        spyOn(preferences, 'get').and.callFake(name => {
            if (name === 'theme') {
                return 'Test';
            }
        });

        expect(themeManager.getParam('navbar')).toBe('side');
    });

    it('param from preferences', () => {
        spyOn(preferences, 'get').and.callFake(name => {
            if (name === 'theme') {
                return 'Test';
            }

            if (name === 'themeParams') {
                return {
                    navbar: 'top',
                };
            }
        });

        expect(themeManager.getParam('navbar')).toBe('top');
    });

    it('param from config', () => {
        spyOn(preferences, 'get').and.callFake(name => {
            if (name === 'theme') {
                return null;
            }
        });

        spyOn(config, 'get').and.callFake(name => {
            if (name === 'themeParams') {
                return {
                    navbar: 'top',
                };
            }
        });

        expect(themeManager.getParam('navbar')).toBe('top');
    });

    it('mapped param 1', () => {
        spyOn(preferences, 'get').and.callFake(name => {
            if (name === 'theme') {
                return 'Test';
            }

            if (name === 'themeParams') {
                return {
                    navbar: 'top',
                };
            }
        });

        expect(themeManager.getParam('navbarHeight')).toBe(43);
    });

    it('mapped param 2', () => {
        spyOn(preferences, 'get').and.callFake(name => {
            if (name === 'theme') {
                return 'Test';
            }

            if (name === 'themeParams') {
                return {
                    navbar: 'side',
                };
            }
        });

        expect(themeManager.getParam('navbarHeight')).toBe(30);
    });
});
