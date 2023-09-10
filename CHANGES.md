[unreleased]
* WPCS 3.0.0

#### 2.0.2 / 2023-02-05
* add variable check

#### 2.0.1 / 2022-11-30
* add git icon to subtab

#### 2.0.0 / 2022-04-24
* require PHP 7.2+

#### 1.2.1 / 2022-03-21
* add 'release_asset_response' to selectively use redirect

#### 1.2.0 / 2021-11-15
* use new filter to add repository types to Git Updater Additions

#### 1.1.0 / 2021-07-05
* updated PHP 5.6 compatibility, will remove when WP core changes minimum requirement

#### 1.0.1 / 2021-05-21
* removed old query arg authentication
* update readme
* add language pack updating

#### 1.0.0 / 2021-05-11
* update logo branding

#### 0.9.2 / 2021-04-25
* fix option name

#### 0.9.1 / 2021-04-12
* fix PHP error, filter must return value

#### 0.9.0 / 2021-04-11
* remove branch set from constructor

#### 0.8.1 / 2021-04-05
* update hooks
* update assets

#### 0.8.0 / 2021-03-18
* update namespacing
* requires Git Updater

#### 0.7.1 / 2021-03-16
* add filter `gu_parse_api_branches`
* add filter `gu_display_repos`
* add filter `gu_running_git_servers`
* add filter `gu_decode_response`

#### 0.7.0 / 2021-03-15 🎂
* add filter `gu_post_api_response_body`
* add filter `gu_get_git_icon_data`
* add filter `gu_parse_enterprise_headers`, updated/renamed `gu_parse_headers_enterprise_api`
* more tests added

#### 0.6.0 / 2021-03-13
* remove constructor
* update `$auth_required`
* add some tests
* add filter `gu_get_repo_api`
* add filter `gu_get_auth_header`
* add filter `gu_post_get_credentials`
* add filter `gu_parse_release_asset`
* add filter `gu_parse_headers_enterprise_api`

#### 0.5.0 / 2021-03-12
* de-anonymize hooks
* add filters for language pack processing

#### 0.4.1 / 2021-03-10
* update for filter `gu_api_url_type`

#### 0.4.0 / 2021-03-10
* add data to `gu_api_repo_type_data`
* add filter `gu_install_remote_install` for remote install
* add filter `gu_api_url_type` for API URL data

#### 0.3.1 / 2021-03-08
* update namespace

#### 0.3.0 / 2021-03-07
* update for core plugin restructuring

#### 0.2.0 / 2021-03-07
* removed the API from GitHub Updater to it's own plugin
* update i18n
