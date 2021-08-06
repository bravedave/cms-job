# Job - Workorder Managerment

## Release Notes

<p>&nbsp;</p>

### 6 August, 2021

* **Select Lines** - as with other areas
  * click the index cell (the cell at start of line with number), it toggles between select/unselected and marks the line with a &check;
  * the index cell header will display the number of items selected, if none are selected it display the number of items reported
* **Download Invoices** - a _multistep_ process

<blockquote>

_PreRequisite_ this option only shows up if some of the items selected have invoices.

_Quirk_ if you have items selected which do and don't have invoices, the download will contain a different number of files than selected items

1. Select some items - be sure to include some with invoices
2. _Right Click_ the _Index Header Cell_, if there are some invoices, the option _download invoices_ will be available

* **Example**
  1. _Right Click_ the _Type_ header and filter either invoiced or paid
  1. _Right Click_ The _Index Header_ and _Select All_
  1. _Right Click_ The _Index Header_ again and _download invoices_

</blockquote>

* **Archive Multiple JOBS** - a _multistep_ process

<blockquote>

1. Select some items
2. _Right Click_ the _Index Header Cell_, select _archive selected_

</blockquote>

* **Item Search** - new item search field - search for item, press enter to add

### 5 August, 2021

* **Recurring Instructions** now included on PDF WorkOrder

### 4 August, 2021

* **Recurring** Job types _**confirming**_

<blockquote>
<h5>Confirming a JOB recurrence</h5>

The _authoritative_ record is the most recent occurrence, so to start with, the original record is authoritative ... what happens next ...

* Property Manager can confirm any recurrence at any time
* Once it's confirmed
  1. the originating job is no longer authoritative, it recurrence is disabled
  2. this job (the confirmed job) is now authoratitive, and the recurring progresses of this job

_note_ if you _confirm_ a recurrence _NOT_ the first occurence _(e.g. the second occurence)_ then the first reccurence will disappear - because on the originating record is recurrence is now disabled (hard to explain but makes sense to me)

</blockquote>

* **Recurring** Job types
  * Now calculate out for week, month and year recurring types
  * Recurrence Parameters can be modified at anytime until the recurrence has been _used (i.e. a subsequent job is created off the recurrence)_
* **due date** has been added to matrix, the default sort order is due date ascending and due is a click and sort column
* **Refresh Icon** (Property Tab) - when make changes to the scheduling or recurring jobs, the projected scheduling is not refreshed, although not a problem in matrix, because you can reload the page, it's awkward in the property tab. This icon allows facilitates the refresh.
* **Payment** - can be modified at any time until the job is marked as paid

### 3 August, 2021

* **Sort by Status** has been added to the JOB matrix
* **Recurring** Job types
  * we are now able to define the schedule
  * recurring jobs appear in the list

### 2 August, 2021

* **Quotes**
  * Removed Upload Invoice/Added Upload Quote
  * If a quote is present, status is progressed to _quoted_
  * when a quote is marked complete, it is archived
    * _note, if you removed the complete check, it is not un-archived_

### 30 July, 2021

* **Job Matrix &gt; Sort Columns** - Sort feature has been added to Property and Contractor
* **New Filters** - Job Types and Status can be filtered through context menus on the respective columns headers
* **Statistics** - Dynamic statics are displayed
* **Access Key = _S_** - to return to the search bar, press **&lt;alt&gt;+&lt;S&gt;** and start typing. This is useful if you are well down the page and wish to return to the search box, **&lt;ctrl&gt;+&lt;home&gt;** also behaves similar to this
* **Matrix Context**
  * added _Goto Contractor &lt;primary contact&gt;_
* **Add Job Button** - has moved to be adjacent to the _search box_, and can be acessed from the _property record_

### 28 July, 2021

* **comments** can be made against any job any time
* **Add Item**, **complete** moved around on form
* **creator and updater** of workorder
  * are visible at the top of the form where the dates are displayed
    * you will see updated by &lt;initials&gt;
    * hover on the text to see the full name of the user
  * *special note* - this is not retrospective, it will only be visible after updating/creating new jobs
* **property record** - If the property is a rental property (appears on Rental > Properties) - then, Jobs now have tab of their own in property record

### 26 July, 2021

* **invoiced** and **paid** added to valid status
* **paid**
  * when marking an order *paid* it will be *archived*
  * *paid* makes the workorder readonly

### 14 July, 2021

* **complete** added to valid status
  * makes the job readonly
* **assigned** removed as status

### 13 July, 2021

* **Filter by PM** as with other matrices, right click the property manager and set the filter - in this case it is persistent, so it will remember your setting.

#### Invoices

* A invoice can now be uploaded to the job, and the context menu have additional options where an invoice is detected - including delete and view the invoice
* the matrix has a column to indicate the precense of an invoice
* Like *Email Sent*, uploading an invoice advances the job's status to invoiced automatically and the job becomes readonly
* **Valid Job Status** - expands to include *invoiced* - now *Draft, Sent & Invoiced*

#### Restictions

* Job Edit Screen
  * Order button is disabled until
    1. there is a valid contractor
    2. there are lines on the order
  * Invoice Upload is disabled until
    1. there is a valid contractor
    2. there are lines on the order

#### Tweaks

* **New Button** Go to Menu - It is difficult to get to the side index menu on mobiles - a down arrow adjacent the search box is visible on small interfaces.
* **Total Lines** has been added to the report

### 12 July, 2021

**Archive Job** - available via the Matrix lines Context Menu
> archived jobs can opitionally be included on the matrix and if they are they can be un-archived

**Duplicate Job** - available via the Matrix lines Context Menu
> suggested use - use to duplicate quotes to get 3 quotes

**Invoke Order** - available for *quotes only* via the Matrix lines Context Menu

> this is like the **Duplicate Job**, it does that **plus** marks the duplicate as an order and archives the quote

**Email Sent** - Jobs that have been **Sent** are *readonly*, Jobs get marked as *Sent* by:

* Emailing the *Job* to the contractor
* Manually via the Matrix lines Context Menu - they can be unflagged as sent and this will allow editing again

**Valid Job Status** - Currently just *Draft* and *Sent* (to be expanded)

**Matrix Seach** - Matrix now has search which filters the lines by matching text on the line with text in the search box (same as other areas of program)

#### Restrictions

> _see Jess if you need it these privileges_

* **Categories** - Adding/deleting - can only be done with users who have the privilege
* **Items** - Adding/deleting - can only be done with users who have the privilege

<style>
  .markdown-body h1 { font-size: 1.6rem; }
  .markdown-body h2 { font-size: 1.4rem; }
  .markdown-body h3 { font-size: 1.2rem; }
  .markdown-body h4 { font-size: 1.1rem; }
  .markdown-body h5 { font-size: 1rem; }
  .markdown-body h6 { font-size: .9rem; }
</style>
