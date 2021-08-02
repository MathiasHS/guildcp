<?php if (!defined("IN_GCP")) {
    die();
}

echo "<title>GuildCP &bull; Privacy policy</title>";

$box = new \GuildCP\Box("");
$box->setClass("container mt-5 mb-5");
$cookie_session = \GuildCP\Config::get("cookie.session");
$cookie_accept_cookies = \GuildCP\Config::get("cookie.accept_cookies");

$box->append(
    "
<div class='container'>
<h1>Privacy Policy for GCP</h1>
<p>At GCP, accessible from https://gcpdevel.cxdur.xyz/, one of our main priorities is the privacy of our visitors. This Privacy Policy document contains types of information that is collected and recorded by GCP and how we use it.</p>
<p>If you have additional questions or require more information about our Privacy Policy, do not hesitate to contact us through email at andreasvannebo@gmail.com</p>

<h2>General Data Protection Regulation (GDPR)</h2>
<p>We are a Data Controller of your information.</p>
 
<p>GCP legal basis for collecting and using the personal information described in this Privacy Policy depends on the Personal Information we collect and the specific context in which we collect the information:</p>
<ul class='pl-5'>
    <li>GCP needs to perform a contract with you</li>
    <li>You have given GCP permission to do so</li>
    <li>Processing your personal information is in GCP legitimate interests</li>
    <li>GCP needs to comply with the law</li>
</ul>
  
<p>GCP will retain your personal information only for as long as is necessary for the purposes set out in this Privacy Policy. We will retain and use your information to the extent necessary to comply with our legal obligations, resolve disputes, and enforce our policies.</p> 

<p>If you are a resident of the European Economic Area (EEA), you have certain data protection rights. If you wish to be informed what Personal Information we hold about you and if you want it to be removed from our systems, please contact us. Our Privacy Policy was generated with the help of <a href='https://privacy-policy-template.com/'>GDPR Privacy Policy Template</a> and the <a href='https://termsfeed.com/privacy-policy/generator/'>Privacy Policy Generator from TermsFeed</a>.</p>

<p>In certain circumstances, you have the following data protection rights:</p>
<ul class='pl-5'>
    <li>The right to access, update or to delete the information we have on you.</li>
    <li>The right of rectification.</li> 
    <li>The right to object.</li>
    <li>The right of restriction.</li>
    <li>The right to data portability</li>
    <li>The right to withdraw consent</li>
</ul>

<h2>Personal Data</h2>
<p>While using GuildCP we may ask you to provide us with some personal data that can be used to contact or identify you.
This information may include:</br>
Name</br>
Email address</br>
Cookies</br>
Battle.net Tag, Battle.net id</br>
Battle.net account character information</br>

<h2>Log Files</h2>

<p>GCP follows a standard procedure of using log files. These files log visitors when they visit websites. All hosting companies do this and a part of hosting services' analytics. The information collected by log files include internet protocol (IP) addresses, browser type, Internet Service Provider (ISP), date and time stamp, referring/exit pages, and possibly the number of clicks. These are not linked to any information that is personally identifiable. The purpose of the information is for analyzing trends, administering the site, tracking users' movement on the website, and gathering demographic information.</p>

<h2>Cookies</h2>
<p>We are using cookies to identify you when you are logged in and to provide a secure connection.</p>
<table class='table table-responsive text-light table-borderless'>
    <thead>
        <tr>
            <th>Cookie name</th>
            <th>Purpose</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{$cookie_session}</td>
            <td>These cookies are used to identify you when you are logged in, and are meant to guarantee a secure connection.</td>
        </tr>
        <tr>
            <td>{$cookie_accept_cookies}</td>
            <td>This cookie is used to remember whether or not you have accepted our notice about cookies.</td>
        </tr>
    </tbody>
</table>


<h2>Children's Information</h2>

<p>Another part of our priority is adding protection for children while using the internet. We encourage parents and guardians to observe, participate in, and/or monitor and guide their online activity.</p>

<p>GCP does not knowingly collect any Personal Identifiable Information from children under the age of 13. If you think that your child provided this kind of information on our website, we strongly encourage you to contact us immediately and we will do our best efforts to promptly remove such information from our records.</p>

<h2>Online Privacy Policy Only</h2>

<p>Our Privacy Policy applies only to our online activities and is valid for visitors to our website with regards to the information that they shared and/or collect in GCP. This policy is not applicable to any information collected offline or via channels other than this website.


<h2>How is your personal information protected</h2>
<p>We value your personal information higly, and we do our best to avoid any form of data exposures. Any sensitive data, such as your passwords, are hashed with secure and appropriate methods in order to keep them secret, even in case of being exposed by an attacker.</p>

Your personal information is never shared with or sold to a third party.
<h2>Consent</h2>

<p>By using our website, you hereby consent to our Privacy Policy and agree to its terms.</p>
</div>"
);
echo $box->render();
 