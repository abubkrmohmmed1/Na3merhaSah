import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:equatable/equatable.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import '../../../core/network/api_client.dart';
import '../../../core/storage/local_report_service.dart';

// Events
abstract class ReportEvent extends Equatable {
  @override
  List<Object?> get props => [];
}

class FetchReports extends ReportEvent {}

class SubmitReport extends ReportEvent {
  final double lat;
  final double lng;
  final String description;
  final int categoryId;
  final List<String> images;
  final String workflowStep;
  final Map<String, dynamic> workflowData;

  SubmitReport({
    required this.lat,
    required this.lng,
    required this.description,
    required this.categoryId,
    this.images = const [],
    this.workflowStep = 'review_submit',
    this.workflowData = const {},
  });
}

class UpdateWorkflowStep extends ReportEvent {
  final String step;
  final Map<String, dynamic> stepData;
  
  UpdateWorkflowStep({required this.step, required this.stepData});
}

class SyncOfflineReports extends ReportEvent {}

// States
abstract class ReportState extends Equatable {
  @override
  List<Object?> get props => [];
}

class ReportInitial extends ReportState {}
class ReportLoading extends ReportState {}
class ReportsLoaded extends ReportState {
  final List<dynamic> reports;
  final int pendingCount;
  ReportsLoaded(this.reports, this.pendingCount);
  @override
  List<Object?> get props => [reports, pendingCount];
}
class ReportError extends ReportState {
  final String message;
  ReportError(this.message);
}
class ReportSubmitSuccess extends ReportState {}
class ReportSavedOffline extends ReportState {}
class WorkflowStepUpdated extends ReportState {
  final String currentStep;
  final Map<String, dynamic> workflowData;
  WorkflowStepUpdated(this.currentStep, this.workflowData);
  @override
  List<Object?> get props => [currentStep, workflowData];
}

// Bloc
class ReportBloc extends Bloc<ReportEvent, ReportState> {
  final ApiClient _api = ApiClient();
  final LocalReportService _local = LocalReportService();

  ReportBloc() : super(ReportInitial()) {
    on<FetchReports>((event, emit) async {
      emit(ReportLoading());
      try {
        final connectivityResult = await (Connectivity().checkConnectivity());
        final isOffline = connectivityResult.contains(ConnectivityResult.none);
        
        List<dynamic> remoteReports = [];
        if (!isOffline) {
          final data = await _api.getReports();
          remoteReports = data['data'] ?? [];
        }
        
        final pendingCount = _local.getPendingCount();
        emit(ReportsLoaded(remoteReports, pendingCount));
      } catch (e) {
        emit(ReportsLoaded([], _local.getPendingCount()));
      }
    });

    on<SubmitReport>((event, emit) async {
      emit(ReportLoading());
      
      final connectivityResult = await (Connectivity().checkConnectivity());
      final isOffline = connectivityResult.contains(ConnectivityResult.none);

      final reportData = {
        'lat': event.lat,
        'lng': event.lng,
        'description': event.description,
        'category_id': event.categoryId,
        'images': event.images,
        'workflow_step': event.workflowStep,
        'step_timestamps': event.workflowData['step_timestamps'] ?? {},
        'has_images': event.images.isNotEmpty,
      };

      if (isOffline) {
        await _local.saveReport(reportData);
        emit(ReportSavedOffline());
        add(FetchReports());
      } else {
        try {
          await _api.submitReport(
            lat: event.lat,
            lng: event.lng,
            description: event.description,
            categoryId: event.categoryId,
            imagePaths: event.images,
            workflowStep: event.workflowStep,
            workflowData: event.workflowData,
          );
          emit(ReportSubmitSuccess());
          add(FetchReports());
        } catch (e) {
          // Fallback to local if submission fails
          await _local.saveReport(reportData);
          emit(ReportSavedOffline());
          add(FetchReports());
        }
      }
    });

    on<UpdateWorkflowStep>((event, emit) async {
      final workflowData = {
        'current_step': event.step,
        'step_data': event.stepData,
        'timestamp': DateTime.now().toIso8601String(),
      };
      
      emit(WorkflowStepUpdated(event.step, workflowData));
    });

    on<SyncOfflineReports>((event, emit) async {
      final connectivityResult = await (Connectivity().checkConnectivity());
      if (connectivityResult.contains(ConnectivityResult.none)) return;

      final pending = _local.getPendingReports();
      if (pending.isEmpty) return;

      emit(ReportLoading());
      int successCount = 0;

      for (var report in pending) {
        try {
          await _api.submitReport(
            lat: report['lat'],
            lng: report['lng'],
            description: report['description'],
            categoryId: report['category_id'],
            imagePaths: List<String>.from(report['images'] ?? []),
            workflowStep: report['workflow_step'] ?? 'review_submit',
            workflowData: report['workflow_metadata'] ?? {},
          );
          successCount++;
        } catch (e) {
          // Skip if fails, try next one later
        }
      }

      if (successCount == pending.length) {
        await _local.clearPendingReports();
      }
      
      add(FetchReports());
    });
  }
}
