<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Upload data for letter - PPE</title>
</head>
<body>
@if(request()->has('success'))
<h2 style="color: blue">Upload success</h2>
@endif
<form action="/update_letter" method="post" class="" enctype="multipart/form-data" >
    @csrf
    <h1 class="">Upload data for letter </h1>
    <label for="" class="">
        <p class="">Audio for Hiragana & KataKana letter [public/Letter/a]</p>
        <input type="file" name="LetterA">
    </label>
    <label for="" class="">
        <p class="">Example (audio, image) [public/Letter/example]</p>
        <input type="file" name="LetterExample">
    </label>
    <div class="">
        <br/>
    <button class="" type="submit">Submit</button>
    </div>
</form>
</body>
</html>