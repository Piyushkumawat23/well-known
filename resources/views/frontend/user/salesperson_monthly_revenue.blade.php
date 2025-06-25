@extends('frontend.layouts.user_panel')

@section('panel_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Monthly Revenue') }}</h5>
        </div>
        @if (count($mainOrderArr) > 0)
            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('Month Name')}}</th>
                            <th>{{ translate('Sale Amount')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mainOrderArr as $key => $order)
                            
                                <tr>
                                    <td>{{ $key }}</td>
                                    <td>{{ $order }}</td>
                                </tr>
                            
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    
              	</div>
            </div>
        @endif
    </div>
@endsection



@section('script')
    <script type="text/javascript">
        $('#order_details').on('hidden.bs.modal', function () {
            location.reload();
        })
    </script>

@endsection
