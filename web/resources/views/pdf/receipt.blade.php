<style>
    .page-break {
        page-break-after: always;
    }
</style>

<div style="width: 650px;">
        <div style="float:left;color: #3a61a6;font-size:100%;">
            <b>Zekra Company</b>
            <br>
            <b>Info@zekra.com</b>
        </div>

        <div style="float:right;margin-left: 3px;">
           <font size="5" color="#3a61a6"><b>INVOICE</b></font><br>
            Order Date : {{$order->created_at}}<br>
            Invoice# : {{$order->id}}<br>
            Customer ID : {{$order->user_id}}<br>
        </div>
</div>
<br><br><br><br><br>
<div style="width: 650px;margin-top: 10px;">
        <div style="float:left;">
          <font size="3" color="#3a61a6"><b>Bill To: </b></font><br>
          User Name : {{$order->user->name}}<br>
          Shipping Address : {{$order->shipping_address}}<br>
          Phone : {{$order->shipping_phone}}<br>
        </div>
</div>

<br><br><br><br><br><br><br>

<div class="container">

  <table style="width:650px;border-collapse:collapse" cellpadding="5" cellspacing="5" border="1">
    <thead>
      <tr style="height:40px; width:450px; margin:0;background-color: #3a61a6;color:white;">
       <th style="height:40px; width:40px; margin:0;">
        ITEM #
      </th>
       <th style="height:40px; width:10px; margin:0;">
         Album Name
       </th>
       <th style="height:40px; width:10px; margin:0;">

       Ablum Price
     </th>

     </tr>
 </thead>
 <tbody>

   @foreach($albums as $album)
    <tr style="height:40px; width:450px; margin:0;">
      <td style="height:40px; width:40px; margin:0;">
        {{$loop->iteration}}
      </td>
      <td style="height:40px; width:10px; margin:0;">
        {{$album->album_name}}
      </td>
      <td style="height:40px; width:10px; margin:0;">
        <p style=" margin:0;"> {{$priceVariables['albums']['price']}}   </p>
      </td>
    </tr>
  @endforeach

 </tbody>
</table>
<div style="width: 650px;margin-top: 10px;margin-right: 200px;">
        <div style="float:right;">

          Subtotal: {{ $priceVariables['subtotal'] }} $<br>
          Shipping: {{ $priceVariables['shipping'] }} $<br>
          Taxes Rate: {{ $priceVariables['shippingAddress']['city']['tax'] }} %<br>
          Taxes: {{ $priceVariables['taxes'] }} $<br>
          <p>-------------------<p>

          Total: {{ $priceVariables['total'] }} $<br>
        </div>
</div>


</div>
<br><br>
<div style="width: 650px;margin-top: 10px;margin-right: 700px;">
        <div style="float:center;">
          If you have any questions please contact
          Info@zekra.com

          <p>
          Thank you for using Zekra.<br>
          Regards,<br>
          Zekra
          </p>
        </div>
</div>
