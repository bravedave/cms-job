# Job - Workorder Managerment

## Latest News

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
