<style>
.book-wrapper {
	background-color: #162748;
	position: relative;
	margin: auto;
	display: block;
	margin-top: 30px;
	margin-bottom: 50px;
	width: 245px;
	height: 340px;
}
.text-horizontal, .text-vertical {
	text-transform: uppercase;
	color: #fff;
	font-size: 90%;
	text-align: left;
}
.text-horizontal {
	letter-spacing: 6px;
	display: block;
	padding-top: 15px;
	text-align: left;
	margin-left: 15px;
}
.text-vertical {
	width: 10px;
	height: 100%;
	word-break: break-all;
	line-height: 17px;
	float: left;
}
.book-inner {
	width: 250px;
	height: 350px;
	margin-left: 15px;
}
.book-thumb {
	width: 220px;
	height: 308px;
	margin-top: 15px;
	margin-left: 15px;
}
.bold {
	font-size: 120%;
	font-weight: bold;
}
.text-upper {
	text-transform: uppercase;
}
</style>
<!-- <div class="book-wrapper">
	<span class="text-horizontal">Congratulations</span>
	<div class="book-inner">
		<span class="text-vertical">ongratulations</span>
		<img
			src="<?=$book['thumb']?>"
			class="book-thumb"
		/>
	</div>
</div> -->
<!-- Dear <?=$user['name']?>.<br /><br />
Congratulations on completing your book<br />
<span class="bold text-upper"><?=$book['name']?></span><br /><br />
Your book is available for digital preview.<br />
<a href="<?=$book['url']?>"><b>Click here</b></a> to access it.<br /><br />

<div style="word-break: break-all;">
Now since your book is published, you can share your book with your family and friends.<br />
You can now order your author copies by placing an order on our bookstore OR by clicking "Order" in your "My Books" section. Once you place the order, we will print and ship the book to your address.<br /><br />
<b>Did you know ?</b><br />
You are one step away from earning from your books and becoming a best selling author. Everytime someone buys a printed copy of your book, you make up to 25% as Author Royalty!.<br />
So share your book and become a best selling author.<br />
In case you need any help and support please feel free to write to us on <a href="mailto:support@bribooks.com">support@bribooks.com</a><br /><br />
</div> -->

<br>Dear <?=$user['name']?>.<br /><br />
Congratulations on completing your book<br />
<span class="bold text-upper"><?=$book['name']?></span><br /><br />

<div style="word-break: break-all;">
It is now an official entry in the National Young Authors’ Fair ( NYAF)  2023, India! <br/>
( It will get listed in the BriBooks Book Store in the next 12-14 hours after review.)<br/>
You can now represent your school in NYAF at <?=$user['city']?> & <?=$user['state']?> level and you will also have the opportunity to represent your school at the national level.<br/>  Top Young Authors get featured in NDTV, Disney International HD, Times of India -NIE and Crossword. <br/>
<a href="https://www.youtube.com/watch?v=_S3Vw7w_bbY"><b>Click here</b></a> to check,  how NDTV celebrated the Young Authors of NYAF 2022.<br/><br/>

Now let’s start your journey as an Entrepreneur Author. Getting hold of the printed copy of your book is the first step. It will help you create videos of your book and share with friends ,family and social networks. <br/>
Get the author copy now at Book Link below
</div>

<div class="book-wrapper">
	<span class="text-horizontal">Congratulations</span>
	<div class="book-inner">
		<span class="text-vertical">ongratulations</span>
		<img
			src="<?=$book['thumb']?>"
			class="book-thumb"
		/>
	</div>
</div>

<?=$book['url']?><br/>

<div>
In case you need any help and support please feel free to write to us on <a href="mailto:support@bribooks.com">support@bribooks.com</a><br/><br/>
</div>

