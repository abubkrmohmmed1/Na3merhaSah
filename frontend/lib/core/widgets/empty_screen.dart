import 'package:flutter/material.dart';

class EmptyScreen extends StatelessWidget {
  final String title;
  final IconData icon;

  const EmptyScreen({super.key, required this.title, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, size: 64, color: const Color(0xFFA3AED0)),
          const SizedBox(height: 16),
          Text(
            title,
            style: const TextStyle(
              fontSize: 18,
              color: Color(0xFF6B7280),
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'قريباً',
            style: TextStyle(fontSize: 14, color: Color(0xFFA3AED0)),
          ),
        ],
      ),
    );
  }
}
