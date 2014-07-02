                             BCR Card reader integration for Sugar 7

* What is Business Card Reader?
BCR is a smartphone application that creates a contact entry based on a business card picture. It is available for the iPhone, iPad, Android, BlackBerry and Windows Phone. 
Web site: http://www.shape.ag/en/products/details.php?product=bcr 

* How does BCR work?
1. Take a photo or select a picture from the « Camera Roll ».
2. Scan the business card. BCR will automatically recognize the text and detect the fields.
3. Check the result. You may save to a new contact or merge with an existing contact.

* The Sugar connector for BCR
Ultimately, BCR may send data to an external system. The Sugar connector for BCR will create or update a contact in your Sugar instance.

* Installation
1. Sugar: install the BCR module using the module loader (Sugar 7 any edition).
Upload BCR.zip.
The installation will create and populated a new field on the User module. This field will receive a token that will replace the password when talking to Sugar. This is a security requirement; otherwise your password will be recorded in your network equipments logs. 
The installation will create an email template that you might want to use in order to notify each Sugar user his new credentials when using BCR with Sugar.
2. BCR app: in the BCR export settings, go to the BCR API panel and set the following url:
Recommended:
https://<your domain>/index.php?entryPoint=BCR&u=<user name>&t=<token>
Not recommended:
https://<your domain>/index.php?entryPoint=BCR&u=<user name>&p=<password>
Go to the BCR export Menu, choose one or several business cards, select the last icon (BCR API). Click on “Export”.
Congratulation, the contacts had been created (or updated) in your Sugar instance!

Understand the rules
The importation process into Sugar follows these rules.
Account
Look for an existing account (same name)
- account found: will link the contact to this account. No change applied to the account.
- account not found: create a new account, fill the name and the billing address. The owner is the current user (provided in the credentials). 
Contact
- if new account
   - create a new contact. the owner is the current user.
- if existing account
   Look for a contact (same first and last name, attached to the account).
   - contact found: update the contact. Do not change the owner.
   - contact not found: create the contact, the owner is the current user.
Contact fields: first name, last name, primary address, title, work phone, other phone, email.

Copyright March, 2014 Olivier Nepomiachty - All rights reserved.

Release notes:
v 1.1.10 - 14 March 2014
Original release.
