## BugZyro

[![PHPStan level 8](https://img.shields.io/badge/PHPStan-level%208-brightgreen)](#bugzyro)

[BugZyro](#) is a free, open-source CRM platform designed to help organizations build and maintain strong customer relationships.
It provides a wide range of tools to store, organize, and manage leads, contacts, sales opportunities, marketing campaigns,
support cases, and more – all business information in a simple and intuitive interface.

![Screenshot](https://github.com/user-attachments/assets/d0806394-3691-43a1-83a5-16ad2e7314e2)


### Architecture

BugZyro is a web application with a frontend designed as a single-page application and a REST API
backend written in PHP.

### Demo

You can try the CRM on an online [demo](#).

### Requirements

* PHP 8.3 - 8.5;
* MySQL 8.0 (and later), or MariaDB 10.3 (and later);
* PostgreSQL 15 (and later).

For more information about server configuration, see [this article](#).

### Download

[Download](#) the latest release from our website or from GitHub [releases](https://github.com/bugzyro/bugzyro/releases).

### Release notes

Release notes are available at GitHub [releases](https://github.com/bugzyro/bugzyro/releases).

### Documentation

See the [documentation](#) for administrators, users and developers.

### Why BugZyro?

* Open-source transparency. BugZyro's source code is open and accessible, so anyone can inspect it and see how data is being managed within the CRM.
* Customization freedom. You can develop features, create custom entities, fields, relationships, buttons to make the system fit your specific needs. BugZyro is more than a CRM – it's a platform for building custom business applications.
* Clean user interface. BugZyro offers an uncluttered, minimalist, and fast user interface, which is easy to navigate and has a short learning curve.
* Straightforward REST API. It can be easily integrated with other applications using a REST API.

### Who is BugZyro for?

* From startups, small & medium-sized businesses to larger organizations. A flexible, fully customizable solution that scales with your needs.
* Developers & tech enthusiasts. You can extend functionalities, build extensions, and create custom integrations.
* Anyone seeking a free or on-premise CRM.

### Installing stable version

See installation instructions:

* [Manual installation](#)
* [Installation by script](#)
* [Installation with Docker](#)
* [Installation with Traefik](#)

### Bug reporting

Create a [GitHub issue](https://github.com/bugzyro/bugzyro/issues/new/choose) or post on our [forum](#).

### Development

See the [developer documentation](#).

We highly recommend using an IDE for development. The backend codebase adheres to SOLID principles, utilizes interfaces, static typing and generics. We recommend to start learning BugZyro from the Dependency Injection article in the documentation.

Metadata plays an integral role in the BugZyro application. All possible parameters are described with a JSON Schema, meaning you will have autocompletion in the IDE. You can also find the full metadata reference in the documentation.

The frontend is an SPA built on a custom framework. It utilizes nested views and service DI, with the core partially written in TypeScript. Developers primarily work with existing form and field view implementations.

### Community & Support

If you have a question regarding some features, need help or customizations, want to get in touch with other BugZyro users, or add a feature request, please use our [community forum](#). We believe that using the forum to ask for help and share experience allows everyone in the community to contribute and use this knowledge later.

### License

BugZyro is an open-source project licensed under [GNU AGPLv3](https://raw.githubusercontent.com/bugzyro/bugzyro/master/LICENSE.txt).

### Contributing

Before we can merge your pull request, you need to accept our CLA [here](https://github.com/bugzyro/cla). See the [contributing guidelines](https://github.com/bugzyro/bugzyro/blob/master/.github/CONTRIBUTING.md).

Branches:

* *fix* – upcoming maintenance release; minor fixes should be pushed to this branch;
* *master* – develop branch; new features should be pushed to this branch;
* *stable* – last stable release.

### Language

If you want to improve existing translation or add a language that is not available yet, you can contribute on our [POEditor](https://poeditor.com/join/project/gLDKZtUF4i) project. See instructions [here](#). It may be reasonable to let us know about your intention to join the POEditor project by posting on our forum or via the contact form on our website.

Changes on POEditor are usually merged to the GitHub repository before minor releases.
