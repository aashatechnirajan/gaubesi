@extends('layouts2.superadmin')

@section('content')
<div class="container">
    <h2>Payments List</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Order ID</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Payment Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>{{ $payment->order_id }}</td>
                    <td>{{ $payment->amount }}</td>
                    <td>{{ $payment->payment_method }}</td>
                    <td>{{ $payment->payment_status }}</td>
                    <td>
                        <a href="{{ route('backend.payments.edit', $payment->id) }}" class="btn btn-primary">Edit</a>
                        <form action="{{ route('backend.payments.destroy', $payment->id) }}" method="POST"
                            style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
