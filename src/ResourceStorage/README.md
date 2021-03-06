Resource Storage Service
========================

# Overview

The Resource Storage Service should be the central place where components and plugins should manage their user uploaded files and resources.

Historically ILIAS uses the file system directly in its components to manage files. There are some conventions but within the structure of a component files can be stored freely. Additional features such as versioning of files is always the responsibility of the component or the plugin.

ILIAS 7 offers the Resource Storage Service for all components and plug-ins.

In summary, the service allows to exchange a user upload (UploadResult from the Upload-Service) for a unique resource ID. During this exchange, the service takes care of the correct storage of the data (file as well as metadatan to the file). There is no direct access of the component to the FileSystem, neither by native PHP functions nor by the FileSystem service.
As soon as the UserUpload is needed again, e.g. to display or download it, the Resource-ID can be exchanged for a corresponding delivery method. This process also does not directly access the file system by the component.

```
  Component                 +-+ Resource
                            | | Storage
  +----------+              | | Service
  | UPLOAD   +--------------> |
  +----------+              | |
                            | |
                            | |
  +----------+              | |
  | ID       <--------------+ |
  +----------+              | |
                            | |
                            | |
+---------------------------------+
                            | |
                            | |
  +----------+              | |
  | ID       +--------------> |
  +----------+              | |
                            | |
                            | |
  +----------+              | |
  | CONSUMER <--------------+ |
  +----------+              | |
                            | |
                            | |
                            +-+

```

## Terms

```
+---------------------------------------------------------------------+
| RESOURCE                                                            |
+---------------------------------------------------------------------+
|                                                                     |
| +--------------------+                                              |
| | IDENTIFICATION     |                                              |
| +--------------------+                                              |
|                                                                     |
| +-------------------+  +-------------------+  +-------------------+ |
| | REVISION          |  | REVISION          |  | REVISION          | |
| +-------------------+  +-------------------+  +-------------------+ |
| |                   |  |                   |  |                   | |
| | +---------------+ |  | +---------------+ |  | +---------------+ | |
| | | INFORMATION   | |  | | INFORMATION   | |  | | INFORMATION   | | |
| | +---------------+ |  | +---------------+ |  | +---------------+ | |
| | | "FILE"        | |  | | "FILE"        | |  | | "FILE"        | | |
| | +---------------+ |  | +---------------+ |  | +---------------+ | |
| |                   |  |                   |  |                   | |
| +-------------------+  +-------------------+  +-------------------+ |
|                                                                     |
+---------------------------------------------------------------------+
```

### Resource

A Resource is the abstract term for a "thing" uploaded by a user and passed to the Resource Storage Service for management. The Resource is the combination of
- Identification
- Revisions
- Information about the revisions
- File or its contents (of the respective revisions)
- Stakeholders

### Identification

The identification represents the unique information about which resource is involved. This information is stored by the component itself to be able to retrieve a resource later.

### Revision

Revisions contain the file together with metadata (information) and a version number. This means that a resource can have multiple revisions. The management of these revisions is the responsibility of the Service. Revisions can be added, changed or deleted from outside by the components.

### Information

Information holds general metadata about a revision, such as MimeType, size, title, ... .  This information is stored by the service.

### Consumer

Consumers allow to "consume" a resource. This is the interface closest to the FileSystem that is allowed to start an action with the file of a revision, such as 
- Download
- Get Stream
- Retrieve content
- ...

A set of Comsumers is already part of the service.

### Stakeholder

Although resources are uploaded to ILIAS by users, these users are not only responsible for a resource but also for the component in which the file has been uploaded. The service must be able to retrieve information from the component in order to decide whether a resource is needed at all or not, e.g. during cleanup processes. Or, if a resource is removed by the service for security reasons, the component must be informed about it, in order to carry out further cleanup steps.

### StorageHandler (internal)

In order to be equipped for the future, the Storage Service internally uses so-called StorageHandlers to store files. In the first step this is of course a FileSystem-based storage handler. However, the way is open here to expand this at a later point in time.

# Usage

The use of the service should be as easy as possible from the point of view of the components and Plugins. The components should only communicate with the service according to the principle shown above, i.e. exchange uploads for identifications or exchange an identification for a consumer.

# Examples

In most cases most of the work is already done by the UI inputs and as a form developer you get the identification directly from the service which can then be stored in your component.

In case you want to exchange an upload for an identification yourself, proceed as follows in your component:

```php
<?php
// ...
global $DIC;
$upload_result = $DIC['upload']->getResults()['my_uploaded_file'];
$stakeholder = new ilMyComponentResourceStakeholder();

$identification = $DIC['resource_storage']->upload($upload_result, $stakeholder);

// then store the $identification whereever I need it in my component


```

Suppose we already have a file stored in the Storage Service and would download it in our component. We had stored the identification, with which a corresponding consumer can now be obtained to download the file.

```php
<?php
// ...
global $DIC;
$rid_string = $this->getMyResourceIDasString(); // we get the stored ID of the resource

$identification  = $DIC['resource_storage']->manage()->find($rid_string);
if (null !== $identification) {
    $DIC['resource_storage']->consume()->download($identification)->run();
} else {
    // there is no such resource in the storage service
}
```
Adding a new revision is as simple as that:

```php
<?php
// ...
global $DIC;
$rid_string = $this->getMyResourceIDasString(); // we get the stored ID of the resource

$identification  = $DIC['resource_storage']->manage()->find($rid_string);
$upload_result = $DIC['upload']->getResults()['my_uploaded_file'];
$stakeholder = new ilMyComponentResourceStakeholder();

if (null !== $identification) {
    $DIC['resource_storage']->manage()->appendNewVersion($identification, $upload_result, $stakeholder);
} else {
    // there is no such resource in the storage service
}
```

# Other (involved) Services

- UploadService, see [here](../FileUpload/README.md)
- FileSystem-Service, see [here](../Filesystem/README.md)

# Migration

- Migration will need at least the feature ["Migrate Command"](https://docu.ilias.de/goto_docu_wiki_wpage_6399_1357.html)
- A documentation how you can migrate your component will follow ASAP

# Roadmap

Please see the roapmap of the service in [README.md](ROADMAP.md)

