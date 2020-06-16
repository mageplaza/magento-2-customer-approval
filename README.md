# Customer Approval for Magento 2

[Customer Approval by Mageplaza](http://www.mageplaza.com/magento-2-customer-approval/) is a solution which helps store owner to approve or reject new account registration from customers in specific cases. This is regarded as an effective control method of customer accounts in online stores using Magento 2 platform. 


## 1. Documentation

- [Installation guide](https://www.mageplaza.com/install-magento-2-extension/)
- [User guide](https://docs.mageplaza.com/customer-approval/index.html)
- [Introduction page](http://www.mageplaza.com/magento-2-customer-approval/)
- [Contribute on Github](https://github.com/mageplaza/magento-2-customer-approval)
- [Get Support](https://github.com/mageplaza/magento-2-customer-approval/issues)


## 2. FAQ

**Q: I got error: Mageplaza_Core has been already defined**

A: Read solution [here](https://github.com/mageplaza/module-core/issues/3)

**Q: I have many sales campaigns, I would like give approval automatically or manually for a specific time? Can I do it?**

A: Yes, for each period of your campaigns, you can set the approval to be automatic or manual from Auto Approve section of Customer Approval.

**Q: After a new customer registers an account, how can I inform them to wait for verification?**

A: You can set the message to customers at After-registration notification section. 

**Q: I am an admin. How can I know when a new account has been registered?**

A: From the backend, kindly enable the function sending admin notification emails. You just need to add your emails on recipients part. 

**Q: How can I send customers the notification when their accounts has been approved?**

A: You can configure this at Approve Notification section.


**Q: How can I send customers the notification when their accounts has not been approved?**

A: You can configure this at Not Approve Notification section.

**Q: If customers are not approved to access page, can I redirect them to another page?**

A: Yes, you can do it easily via Redirect CMS Page section from the backend.  


## 3. How to install Customer Approval extension for Magento 2

Install via composer (recommend), run the following command in Magento 2 root folder:

```
composer require mageplaza/module-customer-approval
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```


## 4. Highlight Features


### Auto or manual approval                                                                                                                                                               
One of the most noticeable features of Customer Approval is that the store owner can give accept customers’ account registration automatically or manually.  

In case the approval is automatic, customers will be approved immediately when finishing account registration and can log in easily. By contrast, if store owners would like to control carefully visitors, the approval can be managed manually from store backend. 

This flexibility in the approval methods supports stores in specific purposes and strategies, suitably in various types of business, various strategies in different periods.  

![Auto or manual approval](https://i.imgur.com/ACGRXeO.png)


### Error Notice or redirect

What will happen when customers try to log in without approved accounts? Customer Approval offers two options with different actions: Error Notice or Redirect.

#### Error notice 

In case a customer uses their accounts which have not received permission to log in, an error notice will appear to notify them with a specific message. Admins can set any messages with ease from the backend. 


#### Redirect visitors to another page

In another way, customers whose accounts have not approved yet can be redirected to a specific page configured by the store admin. This is the page which is allowed to freely access without accounts or a simple notice page. 

The store owner can choose redirected URL which suits a particular purpose such as introduction or marketing and so on. This navigation can avoid customers to feel disappointed or annoyed by their login failure. They take time with the recommended page and wait for the account acceptance.

![Error Notice or redirect](https://i.imgur.com/ydIVVPY.gif)


### Mass or Partial Approval
Another feature of Customer Approval is multi-select to approve or disapproved many accounts on the backend list. From admin backend, admins can select one, many or all customer accounts. Then, the select accounts can be approved or disapproved by just one click on a button as Approve or Not Approve quickly.

This helps admin can save a deal of time significantly in case there are a number of customer accounts waiting for verification. Moreover, it is very easy to change the status of already-approved accounts to prevent from login for some reason. 

![Mass or Partial Approval](https://i.imgur.com/zWp4pZG.png)


### Instant notification emails 
Notification emails are supported in this extension. Both customers and admins can be informed instantly on any account updates. 

With admins, when a new account has been registered, they will receive emails with details of customer accounts and remind them of approval. 
Besides, with customers, they will receive the notification emails accordingly when they register accounts successfully and when the accounts are approved or not approved. 

![Instant notification emails](https://i.imgur.com/KZRzrEs.png)

### API is supported

API is generated to support data among systems in stores. API can help systems in collecting the list of approved customer accounts, approving or not newly registered emails. 

API access the extension components so the delivery of functions and information is more flexible. Through API, businesses can update workflows to make them quicker and more productive.


## 5. More Features

### Select customer groups

Set approval for accounts of specific customer groups.

### Notification label

The content of notice message can be customized easily.

### Email template

Templates for emails to admins and customers are supported. 

### Command line

Quickly approve/ disapprove via command lines. 

### Compatible with SMTP

Prevent emails to spam box with [Mageplaza SMTP extension](https://www.mageplaza.com/magento-2-smtp/)

### Mobile friendly 

Properly display on both PC and mobile devices 


## 6. Full Features List

### For store admins

- Enable/ Disable the module 
- Set account approval to be automatic or manual
- Set notification after an account is registered 
- Show error notice when an account is not approved 
- Redirected to another page when an account is not approved 
- Enable sending emails to admins when customers register new accounts
- Select the sender and input recipients of admin notification emails
- Select email templates for admin notification emails 
- Enable sending emails to customers when they register successfully
- Enable sending emails to customers when their accounts are approved 
- Enable sending emails to customers when their accounts are not approved 

### For customers

- Be informed after registering new accounts 
- Be informed when the accounts are approved 
- Be informed when the accounts are not approved 

## 7. User Guide

### 7.1. Configuration

Login to the **Admin Magento**, choose `Stores> Configuration> Customer Approval`.

![](https://i.imgur.com/bEYNaih.gif)


#### 7.1.1. General

![](https://i.imgur.com/1IQ8Tle.png)

- **Enable**: Select `Yes` to turn on the Module and use **Approve Customer Account** function

- **Auto Approve**: If selecting `Yes`, it will automatically Approve when the customer registers in Frontend.

- **After-registration Notification**:
  - Enter a notification when the account is successfully registered.
  - If left blank, the default is "Your account requires approval".

- **Not Approve Customer Login**: Select the **Not Approve Customer Login** notification type and still login:
  - **Show Error**: Will display the **Not Approve Customer Login** error message. Displaying additional **Error Message** field.
    - **Error Message**: Enter the notification when the client account is not accepted or still has not been approved but try to log in. If left blank, the default is "Your account is not approved".
    
    ![](https://i.imgur.com/GxJrvZR.png)
    
  - **Redirect CMS Page**: Select to redirect to the **Not Approve Customer Login page**:
  
  ![](https://i.imgur.com/9RDDRv6.png)
  
    - **For Not Approve Customer Page**
    
    ![](https://i.imgur.com/D40ZRq7.png)

#### 7.1.2. Admin Notification Email

![](https://i.imgur.com/DMI6oQF.png)

- **Enable**:
  - Select "Yes" to turn on email notification for admin when a customer successfully registers an account.
  - Install [Mageplaza_SMTP](https://www.mageplaza.com/magento-2-smtp/) to avoid sending to spam box.

- **Sender**: Select the person to send email to notify admin:

![](https://i.imgur.com/yJ5ygIX.png)

- **Email Template**: Select an email template to notify admin when the customer successfully registered an account. You can go to `Marketing> Email Templates`, select **Add New Template** to choose to create a notification email template.

- **Recipient(s)**:
  - Enter the email who receives the notification when the customer registers the account.
  - You can enter multiple email recipients at the same time and they must be separated by commas.

#### 7.1.3. Customer Notification Email

![](https://i.imgur.com/CIJx0vf.png)

- **Sender**: Select the person who sent the email to notify the customer.

![](https://i.imgur.com/yJ5ygIX.png)

##### 7.1.3.1. Successful Register

- **Enable**: Select "Yes" to enable email notification for customers when Successful Register.

- **Email Template**: Choose an email template to notify customers of successful account registration. You can go to `Marketing> Email Templates`, select **Add New Template** to choose to create a notification email template.
 
##### 7.1.3.2. Approve Notification

- **Enable**: Select "Yes" to enable email notification to customers when approved with a registered account.

- **Email Template**: Choose an email template to notify customers when approved with a registered account. You can go to `Marketing> Email Templates`, select **Add New Template** to choose to create a notification email template.

##### 7.1.3.3. Not Approve Notification

- **Enable**: Select "Yes" to turn on email notifications for customers when not approved with the registered account.

- **Email Template**: Select an email template to notify customers when not approved with a registered account. You can go to `Marketing> Email Templates`, select **Add New Template** to choose to create a notification email template.

### 7.2. Customers

#### 7.2.1. Grid
- Login to the **Magento Admin**, choose `Customers> All Customers`.
- This section lists the information of the registered customer with fields such as **Name, Email, Group, Approval Status, Date of Birth, etc.** Here you can manually approve or not approve at **Action and edit** with any customer you want.

![](https://i.imgur.com/i0c4Q9M.png)

#### 7.2.2. Edit Customer

- Click on `Edit` to edit or approve/not approve any client. With **Approval Status** shows the status of customer account registration.


![](https://i.imgur.com/tViNc7w.png)

## 8. Using API

- You can use the API integrated with Magento to view the Approved Customers, Waiting for Approval and Not Approval Customers when they sign up for an account.
- Here, we use Postman to support this. You can register Postman [here](https://www.getpostman.com/). Also, you can use other apps to support approval and not approval.

### 8.1. Integration with Magento:

#### Step 1: Login to the **Magento Admin**, choose `System> Extensions> Integrations> Add New Integrations` to create new integration.


**Note**: For the API tab you should select **Customers** and **Mageplaza Customer Approval**.

    
![](https://i.imgur.com/Kwo7RJv.png)
    
![](https://i.imgur.com/jK2IONR.png)



#### Step 2: After creating the Integration, please select Activate

![](https://i.imgur.com/nJ2bsr3.png)


#### Step 3: Click `Allow` to get the information of the **Access Token** field.

![](https://i.imgur.com/CknBYeA.png)



### 8.2. Guide for using Postman to get customers list of waiting for approval, approved and not approved accounts. 


#### 8.2.1 To list the approved customers, you can use the GET method:
- For example:
  - Url: http://example.com//rest/V1/customer/id
  - For example: http://example.com/rest/V1/customers/1
  - With Key and Value: Get the information of the **Access Token** field that you have just integrated to fill it out below. For example:  Authorization: bearer access_token và Content-Type: application/json
  - Click Send to get the list of approved customers.

![](https://i.imgur.com/OnFGBBu.png)


#### 8.2.2 Approve with customers who have registered an account are in the status of Pending or Not Approval, you can use POST method.

- `Note`: At the **Body** part, fill in the email you want to approve. As for the **Header** section, fill the same as above with the GET method.

- Example: Url: http://example.com/rest/V1/customer/approve/email


![](https://i.imgur.com/P0NHkTd.png)



#### 8.2.3 Not Approve with customers who have registered an account are in Pending or Approval status, you can use POST method.

- Example: Url: http://example.com/rest/V1/customer/not-approve/email


![](https://i.imgur.com/W7jIVES.png)

## 9. Instructions to run the command to Approve or Not Approve customer accounts

- **Approve**: You want approval when the registered account is in pending status or not approval, please run the following command:

```
php bin/magento customer:approve "email customer"
```
- Example: `php bin/magento customer:approve email"mageplaza@gmail.com"`


- **Not Approve**:

```
php bin/magento customer:notapprove"email customer"
```

- Example: ` php bin/magento customer:notapprove"mageplaza@gmail.com"`



## Note

When installing, you should run the following command to update customer grid:

```
  php bin / magento indexer: reindex customer_grid
  ```
  
When you want to remove the extension, you should go to the database to delete. Access to `eav_attribute` table, in the `attribute_code` column, you find and delete the `is_approved` attribute

 ![](https://i.imgur.com/aiFNWrY.png)


