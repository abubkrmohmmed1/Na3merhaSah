@extends('layouts.admin')

@section('title', 'الجهات الحكومية')
@section('page_title', 'الجهات الحكومية')

@section('styles')
<style>
    .table-container {
        background-color: white;
        border-radius: 16px;
        padding: 0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
    }

    th {
        background-color: #F3F4F6;
        padding: 12px;
        font-size: 14px;
        color: #6B7280;
        font-weight: 700;
        border: 1px solid #E5E7EB;
    }

    td {
        padding: 12px;
        font-size: 14px;
        font-weight: 600;
        border: 1px solid #E5E7EB;
    }

    .btn-add {
        background-color: #4B7DF3;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-family: 'Cairo', sans-serif;
        font-weight: 700;
        cursor: pointer;
        margin-bottom: 20px;
        float: left;
    }
</style>
@endsection

@section('content')
    <button class="btn-add">+ إضافة جهة جديدة</button>
    <div style="clear: both;"></div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>الرقم</th>
                    <th>اسم الجهة</th>
                    <th>عدد البلاغات المحالة</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($entities as $entity)
                    <tr>
                        <td>{{ $entity['id'] }}</td>
                        <td>{{ $entity['name'] }}</td>
                        <td>{{ $entity['reports_count'] }}</td>
                        <td>
                            <span style="color: #10B981;">{{ $entity['status'] }}</span>
                        </td>
                        <td>
                            <button style="background: none; border: none; color: #4B7DF3; cursor: pointer; font-family: 'Cairo'; font-weight: bold;">تعديل</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
